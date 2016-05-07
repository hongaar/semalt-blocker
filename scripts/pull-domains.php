<?php
/**
 * Pull domains from external sources
 */

use Nabble\SemaltBlocker\Blocker;
use Nabble\SemaltBlocker\Domainparser;

require_once('./../vendor/autoload.php');

$includeOldList = false;

// initialize vars
$semaltBlockerSources = Blocker::getBlocklist();
$sources = [
    'sahava' => [
        'url' => 'https://raw.githubusercontent.com/sahava/spam-filter-tool/master/js/spamfilter.js',
        'method' => 'processor_sahava'
    ],
    'piwik' => [
        'url' => 'https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt'
    ],
    'stevie-ray' => [
        'url' => 'https://raw.githubusercontent.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/master/generator/domains.txt'
    ],
    'antispam' => [
        'url' => 'https://raw.githubusercontent.com/antispam/false-referrals/master/false-referrals.txt'
    ],
    'ar-communications' => [
        'url' => 'https://raw.githubusercontent.com/ARCommunications/Block-Referral-Spam/master/blocker.php',
        'method' => 'processor_ar_communications'
    ],
    'flameeyes' => [
        'url' => 'https://raw.githubusercontent.com/Flameeyes/modsec-flameeyes/master/rules/flameeyes_bad_referrers.data'
    ],
    'semalt-blocker' => [
        'url' => '../domains/additional'
    ],
    'desbma' => [
        'url' => 'https://raw.githubusercontent.com/desbma/referer-spam-domains-blacklist/master/spammers.txt'
    ]
];
$spammers = [];
$annotated = (array) json_decode(file_get_contents('../domains/annotated.json'));

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

function processor_ar_communications($raw) {
    $lines = explode(PHP_EOL, $raw);
    $match = false;
    $domains = [];
    foreach($lines as $line) {
        if (trim($line) == '$spams = array (') {
            $match = true;
        } else if (trim($line) == ');') {
            break;
        }

        if ($match) {
            $line = str_replace('",', '', $line);
            $line = str_replace('"', '', $line);
            $line = trim($line);

            $domains[] = $line;
        }

    }
    return $domains;
}

// echo some info
echo "Old list: " . count($semaltBlockerSources) . " sources\n";
echo "Pulling domains from the following sources:\n";

// Clean function
function clean($url, $list = [])
{
    // only hostnames & path
    $url = Domainparser::getHostname($url) . Domainparser::getPath($url);

    // delete redundant subdomains
    $root = Domainparser::getRootDomain($url);
    if (!empty($list) && $root !== Domainparser::getHostname($url) && in_array($root, $list)) {
        $url = '';
    }

    // trailing /
    $url = trim($url, '/');

    // lower case
    $url = strtolower($url);
    $url = trim($url);
    $punicode = new \TrueBV\Punycode();
    $url = iconv("UTF-8", "ISO-8859-1", $punicode->encode($url));

    return $url;
}

// iterate all sources
$sourcesReadme = '';
foreach($sources as $source => $data) {
    $raw = file_get_contents($data['url']);
    if (isset($data['method']) && function_exists($data['method'])) {
        $list = call_user_func($data['method'], $raw);
    } else if (isset($data['regex'])) {
        preg_match_all($data['regex'], $raw, $list);
        $list = array_filter($list[1], function ($v) {
            return filter_var($v, FILTER_VALIDATE_URL) || filter_var('http://' . $v, FILTER_VALIDATE_URL);
        });
    } else {
        $list = explode("\n", $raw);
    }
    echo $source . " contains " . count($list) . " source(s)\n";
    $sourcesReadme .= '| ' . $source . ' | ' . $data['url'] . ' | ' . count($list) . ' |' . PHP_EOL;
    $spammers = array_merge($spammers, $list);

    foreach($list as $url) {
        if (($cleaned = clean($url)) && !isset($annotated['d'.crc32($cleaned . '-' . $source)])) {
            if ($cleaned !== $url) {
                $annotated['d'.crc32($cleaned . '-' . $source)] = (object) [
                    'url'     => $url,
                    'blocked' => $cleaned,
                    'source'  => $source,
                    'added'   => date('c')
                ];
            } else {
                $annotated['d'.crc32($cleaned . '-' . $source)] = (object) [
                    'url'     => $url,
                    'source'  => $source,
                    'added'   => date('c')
                ];
            }
        }
    }
}

uasort($annotated, function($a, $b) {
    if ($a->url.$a->source == $b->url.$b->source) {
        return 0;
    }
    return ($a->url.$a->source < $b->url.$b->source) ? -1 : 1;
});
file_put_contents('../domains/annotated.json', json_encode((object) $annotated, JSON_PRETTY_PRINT));
echo "Updated annotated.json\n";

// merge & cleanup spammers
if ($includeOldList) {
    $spammers = array_merge(Blocker::getBlocklist(), $spammers);
}

// cleanup
foreach($spammers as &$spammer) {
    $spammer = clean($spammer, $spammers);
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
$readme = preg_replace('/\|:---------------:\|:-----------------------:\|(.*?)---/s', '|:---------------:|:-----------------------:|' . PHP_EOL . '| ' . count($spammers) . ' | ' . date('F jS, Y') . ' |' . PHP_EOL . PHP_EOL . '---', $readme);
$readme = preg_replace('/\|-------------------\|------------------------\|---------------------------\|(.*?)##/s', '|-------------------|------------------------|---------------------------|' . PHP_EOL . $sourcesReadme . PHP_EOL . '##', $readme);
file_put_contents('../README.md', $readme);
echo "Updated README.md\n";

echo "Done\n";
exit;