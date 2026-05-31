<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron\TestDouble;

use Kaiseki\WordPress\Cron\CronJobInterface;
use Kaiseki\WordPress\Cron\ScheduleInterface;

final class StubCronJob implements CronJobInterface
{
    public function __construct(
        private readonly string $hook,
        private readonly ScheduleInterface $recurrence,
    ) {
    }

    public function getHook(): string
    {
        return $this->hook;
    }

    public function getRecurrence(): ScheduleInterface
    {
        return $this->recurrence;
    }

    public function run(): void
    {
    }
}
