<?php

use PHPUnit\Framework\TestCase;

use App\Service\Engine\Util;

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
}
