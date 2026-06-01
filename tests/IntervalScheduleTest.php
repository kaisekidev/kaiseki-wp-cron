<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Kaiseki\WordPress\Cron\IntervalSchedule;
use PHPUnit\Framework\TestCase;

final class IntervalScheduleTest extends TestCase
{
    public function testExposesItsConstructorValues(): void
    {
        $schedule = new IntervalSchedule('acme_every_five_minutes', 300, 'Every five minutes');

        self::assertSame('acme_every_five_minutes', $schedule->getName());
        self::assertSame(300, $schedule->getInterval());
        self::assertSame('Every five minutes', $schedule->getDisplay());
    }
}
