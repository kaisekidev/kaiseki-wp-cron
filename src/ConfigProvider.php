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
                'job_handler' => [],
            ],
            'hook' => [
                'provider' => [
                    JobRegistry::class,
                ],
            ],
            'dependencies' => [
                'aliases' => [],
                'factories' => [
                    JobRegistry::class => JobRegistryFactory::class,
                ],
            ],
        ];
    }
}
