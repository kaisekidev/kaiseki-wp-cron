<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

/**
 * A ready-made {@see CustomScheduleInterface} defined inline by its interval.
 *
 * Return one straight from a job when you do not need a dedicated schedule class:
 *
 * ```php
 * public function getRecurrence(): ScheduleInterface
 * {
 *     return new IntervalSchedule('acme_every_five_minutes', 5 * 60, 'Every five minutes');
 * }
 * ```
 */
final readonly class IntervalSchedule implements CustomScheduleInterface
{
    public function __construct(
        private string $name,
        private int $interval,
        private string $display,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }
}
