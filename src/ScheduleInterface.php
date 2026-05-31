<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

/**
 * A WordPress cron schedule, identified by the recurrence name WordPress knows
 * it by.
 *
 * Returned from {@see CronJobInterface::getRecurrence()}. Implemented by the
 * built-in {@see Recurrence} enum and by {@see CustomScheduleInterface} for
 * intervals the package registers on your behalf.
 */
interface ScheduleInterface
{
    /**
     * The recurrence name passed to `wp_schedule_event()`, e.g. `hourly`.
     */
    public function getName(): string;
}
