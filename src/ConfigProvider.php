<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

final class ConfigProvider
{
    /**
     * @return array<mixed>
     */
    public function __invoke(): array
    {
        return [
            'cron' => [
                // list<class-string<CronJobInterface>>
                'jobs' => [],
            ],
            'hook' => [
                'provider' => [
                    CronScheduler::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [],
                'factories' => [
                    CronScheduler::class => CronSchedulerFactory::class,
                ],
            ],
        ];
    }
}
