<?php
/**
 * Test semalt-blocker run time
 */

require_once('./../vendor/autoload.php');

function mockReferer($referer)
{
    $_SERVER["HTTP_REFERER"] = $referer;
}

$c = 100;

echo "Testing empty referer   : ";
mockReferer('');
$time_pre = microtime(true);
$i=$c;while($i--) {
    \Nabble\SemaltBlocker\Blocker::blocked();
}
echo number_format( (microtime(true) - $time_pre) / $c * 1000, 8) . "ms\n";

echo "Testing invalid referer : ";
mockReferer('.NotAnUrl?/');
$time_pre = microtime(true);
$i=$c;while($i--) {
    \Nabble\SemaltBlocker\Blocker::blocked();
}
echo number_format( (microtime(true) - $time_pre) / $c * 1000, 8) . "ms\n";

echo "Testing good referer    : ";
mockReferer('http://www.google.com/?q=query');
$time_pre = microtime(true);
$i=$c;while($i--) {
    \Nabble\SemaltBlocker\Blocker::blocked();
}
echo number_format( (microtime(true) - $time_pre) / $c * 1000, 8) . "ms\n";

echo "Testing bad referer     : ";
$domainlist = \Nabble\SemaltBlocker\Blocker::getBlocklist();
mockReferer($domainlist[array_rand($domainlist)]);
$time_pre = microtime(true);
$i=$c;while($i--) {
    \Nabble\SemaltBlocker\Blocker::blocked();
}
echo number_format( (microtime(true) - $time_pre) / $c * 1000, 8) . "ms\n";