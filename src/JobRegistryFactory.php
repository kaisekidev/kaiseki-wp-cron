<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

use Kaiseki\WordPress\Config\Config;
use Psr\Container\ContainerInterface;

final class JobRegistryFactory
{
    public function __invoke(ContainerInterface $container): JobRegistry
    {
        /** @var list<class-string<JobHandlerInterface>> $classNames */
        $classNames = Config::get($container)->array('cron/job_handler');
        $handlers = Config::initClassMap($container, $classNames);
        return new JobRegistry(...$handlers);
    }
}
