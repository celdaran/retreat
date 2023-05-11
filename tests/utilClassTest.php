<?php

use PHPUnit\Framework\TestCase;

use App\Service\Engine\Util;
use App\Service\Engine\Period;

final class utilClassTest extends TestCase
{
    public function testInterest100(): void
    {
        $interest = Util::calculateInterest(100, 0);
        $this->assertEquals($interest, 0.00);

        $interest = Util::calculateInterest(100, 1);
        $this->assertEquals($interest, 0.08);

        $interest = Util::calculateInterest(100, 2);
        $this->assertEquals($interest, 0.17);

        $interest = Util::calculateInterest(100, 5);
        $this->assertEquals($interest, 0.42);
    }

    public function testInterest1000(): void
    {
        $interest = Util::calculateInterest(1000, 0);
        $this->assertEquals($interest, 0.00);

        $interest = Util::calculateInterest(1000, 1);
        $this->assertEquals($interest, 0.83);

        $interest = Util::calculateInterest(1000, 2);
        $this->assertEquals($interest, 1.67);

        $interest = Util::calculateInterest(1000, 5);
        $this->assertEquals($interest, 4.17);
    }

    public function testInterest500k(): void
    {
        $interest = Util::calculateInterest(500000, 0.001);
        $this->assertEquals($interest, 0.42);

        $interest = Util::calculateInterest(500000, 5);
        $this->assertEquals($interest, 2083.33);

        $interest = Util::calculateInterest(500000, 7.140);
        $this->assertEquals($interest, 2975.00);

        $interest = Util::calculateInterest(500000, 8.836);
        $this->assertEquals($interest, 3681.67);
    }

    public function testPeriodCompare1(): void
    {
        $period1 = new Period(2023, 1);
        $period2 = new Period(2023, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(0, $result);

        //----------------------------------------

        $period1 = new Period(2023, 1);
        $period2 = new Period(2024, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(-1, $result);

        //----------------------------------------

        $period1 = new Period(2024, 1);
        $period2 = new Period(2023, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(1, $result);
    }

    public function testPeriodCompare2(): void
    {
        //----------------------------------------

        $period1 = new Period(2023, 9);
        $period2 = new Period(2024, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(-1, $result);

        //----------------------------------------

        $period1 = new Period(2024, 9);
        $period2 = new Period(2023, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(1, $result);

        //----------------------------------------

        $period1 = new Period(2020, 1);
        $period2 = new Period(2020, 2);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(-1, $result);

        //----------------------------------------

        $period1 = new Period(2020, 2);
        $period2 = new Period(2020, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(1, $result);

        //----------------------------------------

        $period1 = new Period(2020, 12);
        $period2 = new Period(2021, 1);

        $result = Util::periodCompare(
            $period1->getYear(), $period1->getMonth(),
            $period2->getYear(), $period2->getMonth()
        );

        $this->assertEquals(-1, $result);
    }
}
