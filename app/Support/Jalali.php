<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Jalali
{
    private const MONTHS = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    /**
     * @return array{int, int, int} Jalali year, month, day
     */
    public static function fromGregorian(int $year, int $month, int $day): array
    {
        $cumulativeDays = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $leapYear = $month > 2 ? $year + 1 : $year;
        $days = 355666 + (365 * $year) + intdiv($leapYear + 3, 4) - intdiv($leapYear + 99, 100)
            + intdiv($leapYear + 399, 400) + $day + $cumulativeDays[$month - 1];

        $jalaliYear = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jalaliYear += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jalaliYear += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            return [$jalaliYear, 1 + intdiv($days, 31), 1 + ($days % 31)];
        }

        return [$jalaliYear, 7 + intdiv($days - 186, 30), 1 + (($days - 186) % 30)];
    }

    /**
     * @return array{int, int, int} Gregorian year, month, day
     */
    public static function toGregorian(int $year, int $month, int $day): array
    {
        $year += 1595;
        $days = -355668 + (365 * $year) + (intdiv($year, 33) * 8) + intdiv(($year % 33) + 3, 4) + $day
            + ($month < 7 ? ($month - 1) * 31 : (($month - 7) * 30) + 186);

        $gregorianYear = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gregorianYear += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gregorianYear += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gregorianYear += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gregorianDay = $days + 1;
        $isLeap = ($gregorianYear % 4 === 0 && $gregorianYear % 100 !== 0) || $gregorianYear % 400 === 0;
        $monthLengths = [0, 31, $isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        for ($gregorianMonth = 1; $gregorianMonth < 13 && $gregorianDay > $monthLengths[$gregorianMonth]; $gregorianMonth++) {
            $gregorianDay -= $monthLengths[$gregorianMonth];
        }

        return [$gregorianYear, $gregorianMonth, $gregorianDay];
    }

    /**
     * Format a date as «۱۳۷۹/۰۱/۲۸» style Jalali text (Latin digits).
     */
    public static function format(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        [$year, $month, $day] = self::fromGregorian($date->year, $date->month, $date->day);

        return sprintf('%04d/%02d/%02d', $year, $month, $day);
    }

    /**
     * Format a date as «۵ آبان ۱۴۰۵».
     */
    public static function formatLong(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        [$year, $month, $day] = self::fromGregorian($date->year, $date->month, $date->day);

        return $day.' '.self::MONTHS[$month].' '.$year;
    }

    /**
     * Parse «1379/01/28» (Persian, Arabic or Latin digits) into a Gregorian date.
     */
    public static function parse(string $value): ?Carbon
    {
        $normalized = strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        if (! preg_match('#^(\d{4})[/-](\d{1,2})[/-](\d{1,2})$#', $normalized, $matches)) {
            return null;
        }

        [, $year, $month, $day] = array_map('intval', $matches);

        if ($month < 1 || $month > 12 || $day < 1 || $day > ($month <= 6 ? 31 : ($month <= 11 ? 30 : 30))) {
            return null;
        }

        [$gregorianYear, $gregorianMonth, $gregorianDay] = self::toGregorian($year, $month, $day);

        return checkdate($gregorianMonth, $gregorianDay, $gregorianYear)
            ? Carbon::create($gregorianYear, $gregorianMonth, $gregorianDay)->startOfDay()
            : null;
    }
}
