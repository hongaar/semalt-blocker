<?php

$BLOCKLIST = __DIR__ . DIRECTORY_SEPARATOR . 'blocked';

copy(__DIR__ . DIRECTORY_SEPARATOR . './../domains/blocked', $BLOCKLIST);
chmod($BLOCKLIST, 0777);

include 'semaltblocker.php';

$_SERVER["HTTP_REFERER"] = 'http://semalt.com';

try
{
    // Test blocking
    $expected = 'Blocking because referral domain (semalt.com) is found on blocklist';
    $actual = \Nabble\SemaltBlocker\Blocker::blocked(true);
    if (!@assert($expected === $actual)) throw new Exception('Blocking domains failed');

    // Test updating
    file_put_contents($BLOCKLIST, '');
    \Nabble\SemaltBlocker\Updater::update(true);
    $expected = \Nabble\SemaltBlocker\Updater::getNewDomainList();
    $actual = file_get_contents($BLOCKLIST);
    if (!@assert($expected === $actual)) throw new Exception('Updating domains failed');
}
catch (Exception $e)
{
    echo $e->getMessage() . '<br/>' . PHP_EOL;
}

echo 'All tests finished' . PHP_EOL;

unlink($BLOCKLIST);
