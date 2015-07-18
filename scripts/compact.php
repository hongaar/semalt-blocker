<?php
/**
 * Compact semalt-blocker for non-composer installs
 */

require_once('./../vendor/technosophos/PHPCompressor/src/lib/compactor.php');

$source = "./sources.php";
$target = "./../compact/semaltblocker.php";

print "Compacting semalt-blocker";

$compactor = new Compactor($target);

// Use filters like this (Useable for things like stripping debug-only logging):
$compactor->setFilter(function ($in)
{
    $in = str_replace('require "./../vendor/true/punycode/src/Punycode.php";', '', $in);
    $in = str_replace('require "./../src/SemaltBlocker/Domainparser.php";', '', $in);
    $in = str_replace('require "./../src/SemaltBlocker/Updater.php";', '', $in);
    $in = str_replace('require "./../src/SemaltBlocker/Blocker.php";', '', $in);
    $in = str_replace("'./../../domains/blocked'", "'blocked'", $in);
    return $in;
});
$compactor->compactAll($source);

$compactor->report();
$compactor->close();

print "Testing test.php output:" . PHP_EOL;
passthru('php ./../compact/test.php');