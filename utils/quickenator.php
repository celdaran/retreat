<?php

$i = 0;
$previousRow = [];
$output = [];

if (($handle = fopen("quicken-merged.csv", "r")) !== false) {
    while (($currentRow = fgetcsv($handle, 1024, ",")) !== false) {
        if ($i > 0) {
            if ($currentRow[0] === '') {
                $currentRow[0] = $previousRow[0];
                $currentRow[1] = $previousRow[1];
                $currentRow[2] = $previousRow[2];
                $currentRow[3] = $previousRow[3];
            }
        }
        $output[] = $currentRow;
        $previousRow = $currentRow;
        $i++;
    }
    fclose($handle);
}

$fp = fopen('quicken-merged-fixed', 'a');
foreach ($output as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
