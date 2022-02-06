<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

use Kaiseki\WordPress\Hook\HookCallbackProviderInterface;

interface JobHandlerInterface extends HookCallbackProviderInterface
{
    public function getHookName(): string;

    public function getTimestamp(): int;

    public function getRecurrence(): string;
}
