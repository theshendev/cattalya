<?php

namespace Tests\Unit;

use App\Support\Jalali;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class JalaliTest extends TestCase
{
    public function test_it_converts_known_dates_in_both_directions(): void
    {
        $this->assertSame([1403, 1, 1], Jalali::fromGregorian(2024, 3, 20));
        $this->assertSame([1378, 10, 11], Jalali::fromGregorian(2000, 1, 1));
        $this->assertSame([2000, 4, 16], Jalali::toGregorian(1379, 1, 28));
        $this->assertSame([2024, 3, 20], Jalali::toGregorian(1403, 1, 1));
    }

    public function test_it_formats_and_parses_persian_dates(): void
    {
        $date = Carbon::create(2000, 4, 16);

        $this->assertSame('1379/01/28', Jalali::format($date));
        $this->assertSame('28 فروردین 1379', Jalali::formatLong($date));
        $this->assertTrue(Jalali::parse('۱۳۷۹/۰۱/۲۸')->isSameDay($date));
        $this->assertTrue(Jalali::parse('1379-1-28')->isSameDay($date));
    }

    public function test_it_rejects_invalid_input(): void
    {
        $this->assertNull(Jalali::parse('hello'));
        $this->assertNull(Jalali::parse('1379/13/01'));
        $this->assertNull(Jalali::parse('1379/07/31'));
    }
}
