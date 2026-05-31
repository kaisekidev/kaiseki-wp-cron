<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Brain\Monkey\Functions;
use Kaiseki\Test\WordPress\Cron\TestDouble\StubCronJob;
use Kaiseki\Test\WordPress\Cron\TestDouble\TestContainer;
use Kaiseki\WordPress\Cron\CronSchedulerFactory;
use Kaiseki\WordPress\Cron\Recurrence;
use Mockery;

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

        Functions\when('wp_get_scheduled_event')->justReturn(false);
        Functions\when('get_option')->justReturn([]);
        Functions\when('update_option')->justReturn(true);
        Functions\expect('wp_schedule_event')
            ->once()
            ->with(Mockery::type('int'), 'hourly', 'acme_factory_hook');

        $scheduler->addHooks();

        self::assertSame(10, has_action('acme_factory_hook', [$job, 'run']));
    }
}
