<?php

$i = 0;
$previousRow = [];
$output = [];

$startDate = strtotime('2018-01-01 00:00:00');
$endDate = strtotime('2022-12-31 23:59:59');

$targetArray = [
    '2018' => [
        '01' => [
            'gas' => 100.00,
            'thing' => 50.00,
        ]
    ]
];

$result = [];

if (($handle = fopen("quicken-merged-fixed-cleaned2-5yr.csv", "r")) !== false) {
    while (($currentRow = fgetcsv($handle, 1024, ",")) !== false) {
        if ($i > 0) {
            $transactionDate = strtotime($currentRow[0]);
            if ($startDate <= $transactionDate && $transactionDate <= $endDate) {
                // Only look at items in this recent five-year period
                $transactionCategory = getCategory($currentRow[5], 1);
                $transactionAmount = getAmount($currentRow[8]);
                increase2(
                    date('Y', $transactionDate),
//                    date('m', $transactionDate),
                    $transactionCategory,
                    $transactionAmount
                );
            }
        }
        $output[] = $currentRow;
        $previousRow = $currentRow;
        $i++;
    }
    fclose($handle);
}

// create averages

$sum = [];
foreach ($result as $year => $categories) {
    foreach ($categories as $category => $amount) {
        if (array_key_exists($category, $sum)) {
            $sum[$category] += $amount;
        } else {
            $sum[$category] = $amount;
        }
    }
}

$average = [];
foreach ($sum as $category => $amount) {
    $average[$category] = $amount / 5 / 12;
}

foreach ($average as $category => $amount) {
    echo $category . "," . round($amount, 2) . "\n";
}

#echo "All done\n";

//$fp = fopen('quicken-merged-fixed', 'a');
//foreach ($output as $row) {
//    fputcsv($fp, $row);
//}
//fclose($fp);


function getCategory($category, $depth = 1): string
{
    $part = explode(':', $category);
    switch ($depth) {
        default:
        case 1:
            return $part[0];
        case 2:
            if (count($part) > 1) {
                return $part[0] . ':' . $part[1];
            } else {
                return $part[0];
            }
    }
}

function getAmount($money): float
{
    $isNegative = strpos($money, '(');

    $money = str_replace('(', '', $money);
    $money = str_replace(')', '', $money);

    $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    $amount = $formatter->parseCurrency(trim($money), $curr);

    if ($amount !== false) {
        return $isNegative ? -$amount : $amount;
    } else {
        die("Could not parse $money");
    }
}

function increase(string $year, string $month, string $cat, float $amount)
{
    global $result;
    if (array_key_exists($year, $result)) {
        if (array_key_exists($month, $result[$year])) {
            if (array_key_exists($cat, $result[$year][$month])) {
                $result[$year][$month][$cat] += $amount;
            } else {
                $result[$year][$month][$cat] = 0.00;
                increase($year, $month, $cat, $amount);
            }
        } else {
            $result[$year][$month] = [];
            increase($year, $month, $cat, $amount);
        }
    } else {
        $result[$year] = [];
        increase($year, $month, $cat, $amount);
    }
}

function increase2(string $year, string $cat, float $amount)
{
    global $result;
    if (array_key_exists($year, $result)) {
        if (array_key_exists($cat, $result[$year])) {
            $result[$year][$cat] += $amount;
        } else {
            $result[$year][$cat] = 0.00;
            increase2($year, $cat, $amount);
        }
    } else {
        $result[$year] = [];
        increase2($year, $cat, $amount);
    }
}
