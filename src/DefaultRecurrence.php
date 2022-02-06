<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Cron;

final class DefaultRecurrence
{
    private const VALUE_DAILY = 'daily';
    private const VALUE_TWICE_DAILY = 'twicedaily';
    private const VALUE_HOURLY = 'hourly';

    public static function daily(): string
    {
        return self::VALUE_DAILY;
    }

    public static function twiceDaily(): string
    {
        return self::VALUE_TWICE_DAILY;
    }

    public static function hourly(): string
    {
        return self::VALUE_HOURLY;
    }
}
