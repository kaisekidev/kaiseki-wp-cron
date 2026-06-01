<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

/**
 * A custom cron recurrence — an interval WordPress does not ship with.
 *
 * Return one from {@see CronJobInterface::getRecurrence()} and {@see CronScheduler}
 * adds it to WordPress' list of schedules (the `cron_schedules` filter) before
 * scheduling the event — so there is no separate registration step. Use the
 * ready-made {@see IntervalSchedule}, or implement this interface for a reusable,
 * named schedule shared across jobs.
 */
interface CustomScheduleInterface extends ScheduleInterface
{
    /**
     * The number of seconds between runs.
     */
    public function getInterval(): int;

    /**
     * The human-readable label, shown by tools such as WP Crontrol.
     */
    public function getDisplay(): string;
}
