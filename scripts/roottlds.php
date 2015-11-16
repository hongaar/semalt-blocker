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

    $tlds = $sep . '.' . implode($sep . '.', $matches[1]) . $sep;

} else {

    if ($method === METHOD_PUBLICSUFFIX) {

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

        $tlds = $sep . glue('', (array) $publicSuffixList, $sep);
    }
}

$tlds = $punycode->encode(html_entity_decode($tlds));

$domainParserContents = file_get_contents($domainParserFile);

$regex = '/private static \$rootTlds = .*;/';

$domainParserContents = preg_replace($regex, 'private static $rootTlds = \'' . $tlds . '\';',
    $domainParserContents);

file_put_contents($domainParserFile, $domainParserContents);

echo "Got " . substr_count($tlds, ',') . " root tld's, done.\n";
exit;