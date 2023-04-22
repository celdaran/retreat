<?php namespace App\Service\Engine;

class Util {

    /**
     * Calculate compound interest
     */
    public function calculateInterest(float $p, float $r): float
    {
        // Convert rate
        $r = $r / 100;

        // Set time
        $t = 1 / 12;

        // Calculate new value
        $v = $p * (1 + $r / 12) ** (12 * $t);

        // Return *just* the interest
        return round($v - $p, 2);
    }

    /**
     * Add a number of months to a year/month and return a new year/month
     */
    public function addMonths(int $year, int $month, int $count)
    {
        // First move years ahead by an integer number of years
        $newYear = $year + intdiv($count, 12);

        // Then add modulus of above as months
        $newMonth = $month + $count % 12;

        // If we went over, then roll to the next year
        if ($newMonth > 12) {
            $newMonth -= 12;
            $newYear++;
        }

        // Return a structure
        return [
            'year' => $newYear,
            'month' => $newMonth,
        ];
    }

}