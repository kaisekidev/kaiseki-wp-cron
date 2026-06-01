<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Kaiseki\WordPress\Cron\ConfigProvider;
use Kaiseki\WordPress\Cron\CronScheduler;
use Kaiseki\WordPress\Cron\CronSchedulerFactory;
use PHPUnit\Framework\TestCase;

final class ConfigProviderTest extends TestCase
{
    public function testInvokeReturnsBaselineConfig(): void
    {
        self::assertSame(
            [
                'cron' => [
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
            ],
            (new ConfigProvider())()
        );
    }
}
