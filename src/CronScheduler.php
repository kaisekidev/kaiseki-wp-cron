<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

use Kaiseki\WordPress\Hook\HookProviderInterface;

use function add_action;
use function add_filter;
use function get_option;
use function in_array;
use function is_array;
use function is_string;
use function time;
use function update_option;
use function wp_clear_scheduled_hook;
use function wp_get_scheduled_event;
use function wp_schedule_event;

/**
 * Registers every configured {@see CronJobInterface} with WordPress and keeps the
 * scheduled events in sync.
 *
 * This is the single {@see HookProviderInterface} the package activates. It wires
 * each job's action callback and registers custom recurrences (the `cron_schedules`
 * filter) immediately — both must be in place whenever WP-Cron fires — and defers
 * the actual scheduling to `init`, where WordPress is fully booted. On `init` it
 * schedules the event if it is missing, reschedules it when the recurrence changed
 * and clears events for jobs removed from configuration — replacing the
 * `wp_next_scheduled()` / `wp_schedule_event()` boilerplate hand-written in each
 * cron class.
 */
final class CronScheduler implements HookProviderInterface
{
    /**
     * Option that records the hooks this scheduler manages, so events can be
     * cleared once their job is removed from configuration.
     */
    private const MANAGED_HOOKS_OPTION = 'kaiseki_cron_managed_hooks';

    /** @var list<CronJobInterface> */
    private array $jobs;

    /**
     * @param list<CronJobInterface> $jobs
     */
    public function __construct(array $jobs = [])
    {
        $this->jobs = $jobs;
    }

    public function addHooks(): void
    {
        foreach ($this->jobs as $job) {
            add_action($job->getHook(), [$job, 'run']);
        }
        // Registered unconditionally and early: WordPress consults `cron_schedules`
        // whenever it (re)schedules an event, including while running due events, so
        // a job's custom recurrence must resolve even before `init` runs. The filter
        // is a no-op when no job uses a custom schedule.
        add_filter('cron_schedules', [$this, 'addCustomSchedules']);
        // Defer the actual scheduling until WordPress is fully booted.
        add_action('init', [$this, 'syncEvents']);
    }

    /**
     * Add every custom schedule a job uses to WordPress' list of recurrences.
     *
     * @param mixed $schedules
     *
     * @return array<string, array{interval: int, display: string}>
     */
    public function addCustomSchedules(mixed $schedules): array
    {
        /** @var array<string, array{interval: int, display: string}> $result */
        $result = is_array($schedules) ? $schedules : [];
        foreach ($this->jobs as $job) {
            $schedule = $job->getRecurrence();
            if (!$schedule instanceof CustomScheduleInterface) {
                continue;
            }
            $result[$schedule->getName()] = [
                'interval' => $schedule->getInterval(),
                'display' => $schedule->getDisplay(),
            ];
        }

        return $result;
    }

    /**
     * Schedule every configured job and drop events for jobs that are gone.
     *
     * Runs on `init` (see {@see addHooks()}). Idempotent: it only writes when the
     * schedule is actually missing, the recurrence changed, or a job was removed —
     * so on a steady-state request it just reads the (autoloaded) cron and
     * managed-hooks state and returns without touching the database.
     */
    public function syncEvents(): void
    {
        $active = [];
        foreach ($this->jobs as $job) {
            $this->scheduleJob($job);
            $active[] = $job->getHook();
        }
        $this->clearOrphanedEvents($active);
    }

    private function scheduleJob(CronJobInterface $job): void
    {
        $hook = $job->getHook();
        $name = $job->getRecurrence()->getName();
        $event = wp_get_scheduled_event($hook);
        if ($event === false) {
            wp_schedule_event(time(), $name, $hook);

            return;
        }
        // The recurrence changed since the event was scheduled: reschedule so the
        // new interval takes effect — WordPress keeps the old one otherwise.
        if ($event->schedule !== $name) {
            wp_clear_scheduled_hook($hook);
            wp_schedule_event(time(), $name, $hook);
        }
    }

    /**
     * @param list<string> $active
     */
    private function clearOrphanedEvents(array $active): void
    {
        /** @var mixed $stored */
        $stored = get_option(self::MANAGED_HOOKS_OPTION, []);
        $managed = is_array($stored) ? $stored : [];
        foreach ($managed as $hook) {
            if (!is_string($hook) || in_array($hook, $active, true)) {
                continue;
            }
            wp_clear_scheduled_hook($hook);
        }
        if ($managed === $active) {
            return;
        }
        // Autoloaded: syncEvents() reads it on every request, so loading it with
        // the bulk autoloaded options avoids a separate query each time.
        update_option(self::MANAGED_HOOKS_OPTION, $active, true);
    }
}
