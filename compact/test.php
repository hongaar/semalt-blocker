<?php

$BLOCKLIST = __DIR__ . DIRECTORY_SEPARATOR . 'blocked';

copy(__DIR__ . DIRECTORY_SEPARATOR . './../domains/blocked', $BLOCKLIST);
chmod($BLOCKLIST, 0777);

include 'semaltblocker.php';

$_SERVER["HTTP_REFERER"] = 'http://semalt.com';

try
{
    // Test blocking
    $expected = 'Blocking because referer root domain (semalt.com) is found on blocklist';
    $actual = \Nabble\SemaltBlocker\Blocker::blocked(true);
    if (!@assert($expected === $actual)) throw new Exception('Blocking domains failed');

    // Test updating
    file_put_contents($BLOCKLIST, '');
    \Nabble\SemaltBlocker\Updater::update(true);
    $expected = \Nabble\SemaltBlocker\Updater::getNewDomainList();
    $actual = file_get_contents($BLOCKLIST);
    if (!@assert($expected === $actual)) throw new Exception('Updating domains failed');

    echo chr(27).'[42m'.'All tests passed'.chr(27).'[0m' . PHP_EOL;
}
catch (Exception $e)
{
    echo chr(27).'[41m'.$e->getMessage().chr(27).'[0m' . PHP_EOL;
}

echo 'Testing finished' . PHP_EOL;

unlink($BLOCKLIST);
