<?php
/**
 * Export domains to various formats
 */

require_once('./../vendor/autoload.php');

$domainsDir = __DIR__ . DIRECTORY_SEPARATOR . './../domains/';

$domains = \Nabble\SemaltBlocker\Blocker::getBlocklist();

file_put_contents($domainsDir . 'blocked.json', json_encode($domains, JSON_PRETTY_PRINT) . PHP_EOL);
echo "Written json file\n";

file_put_contents($domainsDir . 'blocked.csv', implode(PHP_EOL, array_map(function($item) {
    return '"' . $item . '"';
}, $domains)) . PHP_EOL);
echo "Written csv file\n";

$xml = "<domains>" . PHP_EOL;
foreach($domains as $domain) {
    $xml .= "\t<domain>" . $domain . "</domain>" . PHP_EOL;
}
$xml .= "</domains>" . PHP_EOL;
file_put_contents($domainsDir . 'blocked.xml', $xml);
echo "Written xml file\n";

$htaccess = "<IfModule mod_setenvif.c>" . PHP_EOL;
foreach($domains as $domain) {
    $htaccess .= "\tSetEnvIfNoCase Referer " . $domain . " spambot=yes" . PHP_EOL;
}
$htaccess .= "</IfModule>";
file_put_contents($domainsDir . 'blocked.conf', $htaccess);
echo "Written apache conf file\n";

echo "Done\n";
exit;