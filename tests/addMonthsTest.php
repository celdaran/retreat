<?php

use PHPUnit\Framework\TestCase;

use App\Service\Engine\Util;

final class addMonthsTest extends TestCase
{
    private static $util;

    public static function setUpBeforeClass(): void
    {
        self::$util = new Util();
    }

    public function testAddMonthsCurrentYear(): void
    {
        $year = 2020;
        $month = 1;

        for ($monthsToAdd = 0; $monthsToAdd < 12; $monthsToAdd++) {
            $result = self::$util->addMonths($year, $month, $monthsToAdd);
            $this->assertEquals($result, ['year' => 2020, 'month' => $month + $monthsToAdd]);
        }
    }

    public function testAddMonthsIntoNextYear(): void
    {
        $year = 2020;
        $month = 2;

        for ($monthsToAdd = 0; $monthsToAdd < 11; $monthsToAdd++) {
            $result = self::$util->addMonths($year, $month, $monthsToAdd);
            $this->assertEquals($result, ['year' => 2020, 'month' => $month + $monthsToAdd]);
        }

        $result = self::$util->addMonths($year, $month, 11);
        $this->assertEquals($result, ['year' => 2021, 'month' => 1]);
    }

    public function testAddTwoYears(): void
    {
        $year = 2020;
        $month = 1;

        $result = self::$util->addMonths($year, $month, 24);
        $this->assertEquals($result, ['year' => 2022, 'month' => 1]);
    }

    public function testAddTwoYearsFromFebruary(): void
    {
        $year = 2020;
        $month = 2;

        $result = self::$util->addMonths($year, $month, 24);
        $this->assertEquals($result, ['year' => 2022, 'month' => 2]);
    }

    public function testAddTwoYearsFromDecember(): void
    {
        $year = 2020;
        $month = 12;

        $result = self::$util->addMonths($year, $month, 24);
        $this->assertEquals($result, ['year' => 2022, 'month' => 12]);
    }

    public function testAddALotOfMonths(): void
    {
        $year = 2020;
        $month = 7;

        $result = self::$util->addMonths($year, $month, 334);
        $this->assertEquals($result, ['year' => 2048, 'month' => 5]);
    }

}
