<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

use function wp_clear_scheduled_hook;
use function wp_next_scheduled;
use function wp_schedule_event;

final class JobRegistry
{
    /** @var list<JobHandlerInterface> */
    private array $jobHandlers;

    public function __construct(JobHandlerInterface ...$jobHandlers)
    {
        $this->jobHandlers = $jobHandlers;
    }

    public function scheduleEvents(): void
    {
        foreach ($this->jobHandlers as $jobHandler) {
            if (wp_next_scheduled($jobHandler->getHookName()) !== false) {
                continue;
            }
            wp_schedule_event($jobHandler->getTimestamp(), $jobHandler->getRecurrence(), $jobHandler->getHookName());
        }
    }

    public function clearScheduledEvents(): void
    {
        foreach ($this->jobHandlers as $jobHandler) {
            wp_clear_scheduled_hook($jobHandler->getHookName());
        }
    }
}
