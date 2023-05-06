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

}
