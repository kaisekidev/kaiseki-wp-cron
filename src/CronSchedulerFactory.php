<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

use Kaiseki\Config\Config;
use Psr\Container\ContainerInterface;

use function array_values;

final class CronSchedulerFactory
{
    public function __invoke(ContainerInterface $container): CronScheduler
    {
        $config = Config::fromContainer($container);
        /** @var list<class-string<CronJobInterface>> $jobClasses */
        $jobClasses = $config->array('cron.jobs');
        /** @var list<CronJobInterface> $jobs */
        $jobs = array_values(Config::initClassMap($container, $jobClasses));

        return new CronScheduler($jobs);
    }
}
