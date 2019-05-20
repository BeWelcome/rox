<?php

$dir = realpath(__DIR__ . "/..");
require $dir . '/config/bootstrap.php';

$handle = fopen('alternateNames.txt', 'r');
mkdir('alternatenames');
chdir('alternatenames');
if ($handle) {
    $i = 1;
    $j = 1;
    $sql = '';
    while (($line = fgets($handle)) !== false) {
        $parts = explode("\t", $line);
        if ($parts[2] != '' && $parts[2] != 'link' && $parts[2] != 'wkdt' && $parts[2] != 'post') {
            $sql .=  "REPLACE INTO geonamesalternatenames SET alternatenameId = {$parts[0]}, geonameId = {$parts[1]}, isolanguage = '{$parts[2]}', alternatename = '" . str_replace("'", "\'", $parts[3]) . "'";
            if (isset($parts[4]) && $parts[4] <> '') {
                $sql .= ", ispreferred = {$parts[4]}";
            }
            if (isset($parts[5]) && $parts[5] <> '') {
                $sql .= ", ispreferred = {$parts[5]}";
            }
            if (isset($parts[6]) && $parts[6] <> '') {
                $sql .= ", ispreferred = {$parts[6]}";
            }
            $sql .= ";" . PHP_EOL;
            $i++;
            if ($i % 10000 == 0) {
                $w = 'block-' . $j . '.sql';
                $h = fopen($w, "w");
                fwrite($h, "SET FOREIGN_KEY_CHECKS=0;");
                fwrite($h, $sql);
                fwrite($h, "SET FOREIGN_KEY_CHECKS=1;");
                fclose($h);
                $j++;
                $sql = 'hallo';
            }
        }
    }

    $w = 'block-' . $j . '.sql';
    $h = fopen($w, "w");
    fwrite($h, $sql);
    fclose($h);
    $j++;
    $sql = '';

    fclose($handle);
} else {
    echo "Nothing to do. Please downlad http://download.geonames.org/export/dump/alternateNames.zip and unzip it";
}
