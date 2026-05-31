<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Brain\Monkey\Functions;
use Kaiseki\Test\WordPress\Cron\TestDouble\StubCronJob;
use Kaiseki\WordPress\Cron\CronScheduler;
use Kaiseki\WordPress\Cron\IntervalSchedule;
use Kaiseki\WordPress\Cron\Recurrence;
use Mockery;

use function has_action;
use function has_filter;

final class CronSchedulerTest extends AbstractTestCase
{
    public function testAddHooksRegistersAnActionForEachJob(): void
    {
        $first = new StubCronJob('acme_first', Recurrence::Hourly);
        $second = new StubCronJob('acme_second', Recurrence::Daily);
        $scheduler = new CronScheduler([$first, $second]);

        $scheduler->addHooks();

        self::assertSame(10, has_action('acme_first', [$first, 'run']));
        self::assertSame(10, has_action('acme_second', [$second, 'run']));
    }

    public function testAddHooksDefersSchedulingToInit(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_hook', Recurrence::Hourly)]);

        $scheduler->addHooks();

        self::assertSame(10, has_action('init', [$scheduler, 'syncEvents']));
    }

    public function testAddHooksRegistersTheCronSchedulesFilter(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_hook', Recurrence::Hourly)]);

        $scheduler->addHooks();

        self::assertSame(10, has_filter('cron_schedules', [$scheduler, 'addCustomSchedules']));
    }

    public function testSchedulesTheEventWhenNoneIsScheduledYet(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_hook', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn(true);
        Functions\expect('wp_schedule_event')
            ->once()
            ->with(Mockery::type('int'), 'hourly', 'acme_hook');

        $scheduler->syncEvents();
    }

    public function testDoesNotRescheduleWhenTheRecurrenceIsUnchanged(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_hook', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn($this->scheduledEvent('acme_hook', 'hourly'));
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn(true);
        Functions\expect('wp_schedule_event')->never();
        Functions\expect('wp_clear_scheduled_hook')->never();

        $scheduler->syncEvents();
    }

    public function testReschedulesWhenTheRecurrenceChanged(): void
    {
        // Job now wants 'daily' but the existing event is still 'hourly'.
        $scheduler = new CronScheduler([new StubCronJob('acme_hook', Recurrence::Daily)]);

        Functions\when('wp_get_scheduled_event')->justReturn($this->scheduledEvent('acme_hook', 'hourly'));
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn(true);
        Functions\expect('wp_clear_scheduled_hook')->once()->with('acme_hook');
        Functions\expect('wp_schedule_event')
            ->once()
            ->with(Mockery::type('int'), 'daily', 'acme_hook');

        $scheduler->syncEvents();
    }

    public function testAddCustomSchedulesMergesCustomSchedulesIntoTheExistingOnes(): void
    {
        $schedule = new IntervalSchedule('acme_five_minutes', 300, 'Every five minutes');
        $scheduler = new CronScheduler([
            new StubCronJob('acme_builtin', Recurrence::Hourly),
            new StubCronJob('acme_custom', $schedule),
        ]);

        self::assertSame(
            [
                'hourly' => ['interval' => 3600, 'display' => 'Once Hourly'],
                'acme_five_minutes' => ['interval' => 300, 'display' => 'Every five minutes'],
            ],
            $scheduler->addCustomSchedules(['hourly' => ['interval' => 3600, 'display' => 'Once Hourly']])
        );
    }

    public function testAddCustomSchedulesIgnoresNonArrayInput(): void
    {
        $schedule = new IntervalSchedule('acme_five_minutes', 300, 'Every five minutes');
        $scheduler = new CronScheduler([new StubCronJob('acme_custom', $schedule)]);

        self::assertSame(
            ['acme_five_minutes' => ['interval' => 300, 'display' => 'Every five minutes']],
            $scheduler->addCustomSchedules(null)
        );
    }

    public function testClearsEventsForJobsRemovedFromConfiguration(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_kept', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('wp_schedule_event')->justReturn(true);
        Functions\when('get_option')->justReturn(['acme_kept', 'acme_orphan']);
        Functions\expect('wp_clear_scheduled_hook')->once()->with('acme_orphan');
        Functions\expect('update_option')
            ->once()
            ->with('kaiseki_cron_managed_hooks', ['acme_kept'], true);

        $scheduler->syncEvents();
    }

    public function testToleratesAMissingManagedHooksOption(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_kept', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('wp_schedule_event')->justReturn(true);
        Functions\when('get_option')->justReturn(false);
        Functions\expect('wp_clear_scheduled_hook')->never();
        Functions\expect('update_option')
            ->once()
            ->with('kaiseki_cron_managed_hooks', ['acme_kept'], true);

        $scheduler->syncEvents();
    }

    public function testSkipsNonStringEntriesInTheManagedHooksOption(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_kept', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('wp_schedule_event')->justReturn(true);
        Functions\when('get_option')->justReturn([123, 'acme_orphan', 'acme_kept']);
        Functions\expect('wp_clear_scheduled_hook')->once()->with('acme_orphan');
        Functions\expect('update_option')
            ->once()
            ->with('kaiseki_cron_managed_hooks', ['acme_kept'], true);

        $scheduler->syncEvents();
    }

    public function testDoesNotRewriteTheManagedHooksOptionWhenUnchanged(): void
    {
        $scheduler = new CronScheduler([new StubCronJob('acme_kept', Recurrence::Hourly)]);

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('wp_schedule_event')->justReturn(true);
        Functions\when('get_option')->justReturn(['acme_kept']);
        Functions\expect('wp_clear_scheduled_hook')->never();
        Functions\expect('update_option')->never();

        $scheduler->syncEvents();
    }

    /**
     * Build a stand-in for the object `wp_get_scheduled_event()` returns.
     *
     * @param string $hook
     * @param string $schedule
     */
    private function scheduledEvent(string $hook, string $schedule): object
    {
        return (object)[
            'hook' => $hook,
            'timestamp' => 1,
            'schedule' => $schedule,
            'args' => [],
            'interval' => 3600,
        ];
    }
}
