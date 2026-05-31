<?php

declare(strict_types=1);

namespace Kaiseki\Test\WordPress\Cron;

use Kaiseki\WordPress\Cron\Recurrence;
use PHPUnit\Framework\TestCase;

final class RecurrenceTest extends TestCase
{
    public function testGetNameReturnsTheWordPressScheduleName(): void
    {
        self::assertSame('hourly', Recurrence::Hourly->getName());
        self::assertSame('twicedaily', Recurrence::TwiceDaily->getName());
        self::assertSame('daily', Recurrence::Daily->getName());
        self::assertSame('weekly', Recurrence::Weekly->getName());
    }

    public function testGetNameMatchesTheBackingValueForEveryCase(): void
    {
        foreach (Recurrence::cases() as $case) {
            self::assertSame($case->value, $case->getName());
        }
    }
}
