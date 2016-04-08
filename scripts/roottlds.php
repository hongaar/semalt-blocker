<?php
/**
 * Get root tld's from IANA root db's.
 */

const METHOD_IANA         = 'iana';
const METHOD_PUBLICSUFFIX = 'ps';

$method = METHOD_PUBLICSUFFIX;
$sep = ',';

use Pdp\PublicSuffixListManager;
use TrueBV\Punycode;

require_once('./../vendor/autoload.php');

/**
 * @param object $obj
 * @return object
 */
function with($obj)
{
    return $obj;
}

$punycode = new Punycode();

$domainParserFile = '../src/SemaltBlocker/Domainparser.php';

if ($method === METHOD_IANA) {

    $rootDBUrl = 'http://www.iana.org/domains/root/db';

    $contents = file_get_contents($rootDBUrl);

    $regex = '/<span class="domain tld"><a .*>\.(.*)<\/a>/';

    preg_match_all($regex, $contents, $matches);

    $tlds = explode($sep, '.' . implode($sep . '.', $matches[1]));

} elseif ($method === METHOD_PUBLICSUFFIX) {

    $publicSuffixList = with(new PublicSuffixListManager())->getList();

    $tlds = [];

    function glue($key, $value, $sep = ',')
    {
        if (is_array($value) && !empty($value)) {
            // Ignore exceptions and just return key
            if (key($value) === '*') {
                return '.' . $key . $sep;
            }

            $slds = (empty($key) ? '' : '.' . $key . $sep);
            foreach ($value as $k => $v) {
                $glued = glue($k . (empty($key) ? '' : '.' . $key), $v, $sep);
                if ($glued === false) {
                    $slds .= 'HAI';
                } else {
                    $slds .= !empty($glued) ? $glued : '';
                }
            }
            return $slds;
        } else {
            return '.' . $key . $sep;
        }
    }

    $tlds = explode($sep, glue('', (array) $publicSuffixList, $sep));
}

// Edge case where a roottld is also on blocklist
foreach($tlds as $k => $tld) {
    if (Nabble\SemaltBlocker\Blocker::isUrlOnBlocklist('http://www.' . $tld)) {
        unset($tlds[$k]);
    }
}

$tldsString = trim(implode($sep, $tlds), $sep);
$tldsString = $punycode->encode(html_entity_decode($tldsString));

$domainParserContents = file_get_contents($domainParserFile);

$regex = '/private static \$suffixList = .*;/';

$domainParserContents = preg_replace($regex, 'private static $suffixList = \'' . $tldsString . '\';',
    $domainParserContents);

file_put_contents($domainParserFile, $domainParserContents);

echo "Got " . count($tlds) . " root tld's, done.\n";
exit;