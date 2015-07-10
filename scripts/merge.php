<?php
/**
 * Merge a list with the blocked domains
 */

require_once('./../vendor/autoload.php');

$semaltBlockerSources = \Nabble\Semalt::getBlocklist();

echo "Old list: " . count($semaltBlockerSources) . " sources\n";
echo "Paste domains (one on each line), then press ENTER twice:\n\n";

$handle = fopen ("php://stdin","r");
$newDomains = [];
while($line = fgets($handle)) {

    if(trim($line) == ''){
        echo "Done, got " . count($newDomains) . " sources, new domains is ";

        $newList = array_merge($semaltBlockerSources, $newDomains);

        foreach($newList as &$source) {
            $source = \Nabble\Domainparser::getToplevelDomain($source);
        }

        $newList = array_unique($newList);
        sort($newList);

        echo count($newList) . " sources:\n\n";

        echo implode("\n", $newList) . "\n\n";
        break;
    } else {
        $newDomains[] = trim($line);
    }

}