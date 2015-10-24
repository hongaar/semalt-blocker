<?php
/**
 * Pull domains from external sources
 */

require_once('./../vendor/autoload.php');

$includeOldList = false;

// initialize vars
$semaltBlockerSources = \Nabble\SemaltBlocker\Blocker::getBlocklist();
$sources = [
    'https://raw.githubusercontent.com/sahava/spam-filter-tool/master/js/spamfilter.js' => 'processor_sahava',
    'https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt' => '',
//    'http://lonegoatuk.tumblr.com/post/107307494431/google-analytics-referral-spambot-list' => '/<li>(.*?)<\/li>/',
    'https://raw.githubusercontent.com/Stevie-Ray/htaccess-referral-spam-blacklist-block/master/.htaccess' => '/Referer (.*) spambot=yes/',
    'https://raw.githubusercontent.com/antispam/false-referrals/master/false-referrals.txt' => '',
];
$spammers = [];

function processor_sahava($raw) {
    $lines = explode(PHP_EOL, $raw);
    $match = false;
    $splitter = '/\|(?![a-z|.]+\))/';
    $domains = [];
    foreach($lines as $line) {
        if (trim($line) == 'var filters = [') {
            $match = true;
        } else if (trim($line) == '];') {
            break;
        }

        if ($match) {
            $line = str_replace("',", "", $line);
            $line = str_replace("'", "", $line);
            $line = preg_replace($splitter, '@', $line);
            $regexes = explode('@', $line);
            $regexes = array_map('trim', $regexes);

            foreach($regexes as $regex) {
                if (substr_count($regex, '(')) {
                    $firstPart = substr($regex, 0, strpos($regex, '('));
                    $regexPart = substr($regex, strpos($regex, '('));
                    $regexPart = str_replace(['(', ')'], '', $regexPart);
                    $regexPart = explode('|', $regexPart);
                    foreach ($regexPart as $lastPart) {
                        $domains[] = $firstPart . $lastPart;
                    }
                } else {
                    $domains[] = $regex;
                }
            }
        }

    }
    return $domains;
}

// echo some info
echo "Old list: " . count($semaltBlockerSources) . " sources\n";
echo "Pulling domains from the following sources:\n";

// iterate all sources
foreach($sources as $source => $regex) {
    $raw = file_get_contents($source);
    if (substr_count($regex, 'processor_') && function_exists($regex)) {
        $list = call_user_func($regex, $raw);
    } else if (!empty($regex)) {
        preg_match_all($regex, $raw, $list);
        $list = array_filter($list[1], function ($v) {
            return filter_var($v, FILTER_VALIDATE_URL) || filter_var('http://' . $v, FILTER_VALIDATE_URL);
        });
    } else {
        $list = explode("\n", $raw);
    }
    echo $source . " contains " . count($list) . " source(s)\n";
    $spammers = array_merge($spammers, $list);
}

// only top-level domains
foreach($spammers as &$spammer) {
    $spammer = \Nabble\SemaltBlocker\Domainparser::getRootDomain($spammer);
}

// merge & cleanup spammers
if ($includeOldList) {
    $spammers = array_merge(\Nabble\SemaltBlocker\Blocker::getBlocklist(), $spammers);
}
$spammers = array_map('strtolower', $spammers);
$spammers = array_map('trim', $spammers);
$punicode = new \TrueBV\Punycode();
foreach($spammers as &$spammer) {
    $spammer = iconv("UTF-8", "ISO-8859-1", $punicode->encode($spammer));
}
$spammers = array_unique($spammers);
$spammers = array_filter($spammers);
sort($spammers);

// echo some info
echo "New list: " . count($spammers) . " sources\n";

// write
if (count($spammers))
    file_put_contents('../domains/blocked', implode("\n", $spammers) . PHP_EOL);
echo "Updated blocklist\n";

// readme
$readme = file_get_contents('../README.md');
$readme = preg_replace('/#### Bad domains counter.*/', '#### Bad domains counter: `' . count($spammers) . '` _updated ' . date('F jS, Y') . '_ ', $readme);
file_put_contents('../README.md', $readme);
echo "Updated README.md\n";

echo "Done\n";
exit;