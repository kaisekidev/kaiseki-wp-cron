<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Kaiseki\Test\WordPress\Cron\TestDouble\StubCronJob;
use Kaiseki\Test\WordPress\Cron\TestDouble\TestContainer;
use Kaiseki\WordPress\Cron\CronSchedulerFactory;
use Kaiseki\WordPress\Cron\Recurrence;

use function has_action;

final class CronSchedulerFactoryTest extends AbstractTestCase
{
    public function testBuildsASchedulerThatWiresTheConfiguredJobs(): void
    {
        $job = new StubCronJob('acme_factory_hook', Recurrence::Hourly);
        $container = new TestContainer([
            'config' => [
                'cron' => [
                    'jobs' => [
                        StubCronJob::class,
                    ],
                ],
            ],
            StubCronJob::class => $job,
        ]);

        $scheduler = (new CronSchedulerFactory())($container);
        $scheduler->addHooks();

        self::assertSame(10, has_action('acme_factory_hook', [$job, 'run']));
    }
}
