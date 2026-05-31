<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

/**
 * A single recurring WordPress cron job.
 *
 * Implement this interface and register the class in the `cron.jobs` config key.
 * {@see CronScheduler} wires the action hook, schedules the event and keeps it
 * in sync — you never call `add_action()` or `wp_schedule_event()` yourself.
 */
interface CronJobInterface
{
    /**
     * The unique action hook the event fires, e.g. `acme_cleanup_temp_files`.
     */
    public function getHook(): string;

    /**
     * How often the event recurs.
     *
     * Return a {@see Recurrence} case for the WordPress built-ins, or a
     * {@see CustomScheduleInterface} (e.g. {@see IntervalSchedule}) for a custom
     * interval — the scheduler registers it with WordPress for you.
     */
    public function getRecurrence(): ScheduleInterface;

    /**
     * The work performed each time the event fires.
     */
    public function run(): void;
}
