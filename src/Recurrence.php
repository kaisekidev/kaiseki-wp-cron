<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

/**
 * The recurrences WordPress registers by default.
 *
 * Return one of these from {@see CronJobInterface::getRecurrence()} instead of
 * hard-coding the magic strings. For anything else, return a
 * {@see CustomScheduleInterface} (e.g. {@see IntervalSchedule}) — the package
 * registers it with WordPress for you.
 */
enum Recurrence: string implements ScheduleInterface
{
    case Hourly = 'hourly';
    case TwiceDaily = 'twicedaily';
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function getName(): string
    {
        return $this->value;
    }
}
