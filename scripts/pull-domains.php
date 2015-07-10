<?php
/**
 * Pull domains from external sources
 */

require_once('./../vendor/autoload.php');

// initialize vars
$semaltBlockerSources = \Nabble\Semalt::getBlocklist();
$sources = [
    'https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt' => '',
    'http://lonegoatuk.tumblr.com/post/107307494431/google-analytics-referral-spambot-list' => '/<li>(.*?)<\/li>/',
    'https://raw.githubusercontent.com/Stevie-Ray/htaccess-referral-spam-blacklist-block/master/.htaccess' => '/Referer (.*) spambot=yes/'
];
$spammers = [];

// echo some info
echo "Old list: " . count($semaltBlockerSources) . " sources\n";
echo "Pulling domains from the following sources:\n";
echo implode("\n", array_keys($sources)) . "\n";

// iterate all sources
foreach($sources as $source => $regex) {
    $raw = file_get_contents($source);
    if (!empty($regex)) {
        preg_match_all($regex, $raw, $list);
        $list = array_filter($list[1], function($v) {
            return filter_var($v, FILTER_VALIDATE_URL) || filter_var('http://' . $v, FILTER_VALIDATE_URL);
        });
    } else {
        $list = explode("\n", $raw);
    }
    $spammers = array_merge($spammers, $list);
}

// only top-level domains
foreach($spammers as &$spammer) {
    $spammer = \Nabble\Domainparser::getToplevelDomain($spammer);
}

// merge & cleanup spammers
$spammers = array_merge(\Nabble\Semalt::getBlocklist(), $spammers);
$spammers = array_map('strtolower', $spammers);
$spammers = array_unique($spammers);
$spammers = array_filter($spammers);
sort($spammers);

// echo some info
echo "New list: " . count($spammers) . " sources\n";

// write
if (count($spammers))
    file_put_contents('../domains/blocked', implode("\n", $spammers));

echo "Done\n";
exit;