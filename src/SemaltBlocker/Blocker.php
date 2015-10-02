<?php
namespace Nabble\SemaltBlocker;

/**
 * The most important class for this package. Basic usage:
 * ```
 * Blocker::protect();
 * ```
 * 
 * Alternate flow, with logging, but without trying to auto-update the blocklist:
 * ```
 * use Nabble\SemaltBlocker\Blocker;
 * if (Blocker::isRefererOnBlocklist()) {
 *     error_log(Blocker::getReason());
 *     Blocker::forbidden();
 *     die;
 * }
 * ```
 * 
 * @package Nabble\SemaltBlocker
 */
class Blocker
{
    const SEPERATOR = ':';

    public static $explanation = "Access to this website has been blocked because your referral is set to %s. <a href='%s'>Read why</a>";

    private static $blocklist = './../../domains/blocked';
    private static $debug = 'Not blocking, no reason given';

    //////////////////////////////////////////
    // PUBLIC API                           //
    //////////////////////////////////////////

    /**
     * Block a page if referer is found on list of blocked domains
     *
     * @param string $action If empty, send 403 response; if URL, redirect here; if non-empty string, print message
     */
    public static function protect($action = '')
    {
        // Try to update the list
        if (!defined('SEMALT_UNIT_TESTING')) Updater::update();

        // Simply stop here if referer is not on the list
        if (!self::isRefererOnBlocklist()) return;

        self::doBlock($action);

        // Stop execution altogether, bye bye bots
        if (!defined('SEMALT_UNIT_TESTING')) exit;
    }

    /**
     * @param bool $verbose
     * @return bool|string
     */
    public static function blocked($verbose = false)
    {
        $blocked = self::isRefererOnBlocklist();
        if ($verbose === true) {
            return self::$debug;
        }
        return $blocked;
    }

    public function getReason()
    {
        return self::$debug;
    }

    public static function forbidden()
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' 403 Forbidden');
    }

    /**
     * @return bool
     */
    public static function isRefererOnBlocklist()
    {
        $referer = self::getHttpReferer();
        if ($referer === null) {
            self::$debug = "Not blocking because referral header is not set or empty";
            return false;
        }

        $rootDomain = Domainparser::getRootDomain($referer);
        if ($rootDomain === false) {
            self::$debug = "Not blocking because we couldn't parse referral domain";
            return false;
        }

        if (substr_count(self::getConcatenateBlocklist(), self::SEPERATOR . $rootDomain . self::SEPERATOR) === 0) {
            self::$debug = "Not blocking because referral domain (" . $rootDomain . ") is not found on blocklist";
            return false;
        }

        self::$debug = "Blocking because referral domain (" . $rootDomain . ") is found on blocklist";
        return true;
    }

    /**
     * @return array
     */
    public static function getBlocklist()
    {
        return self::parseBlocklist(self::getBlocklistContents());
    }

    /**
     * @return string
     */
    public static function getBlocklistFilename()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . static::$blocklist;
    }

    //////////////////////////////////////////
    // PRIVATE FUNCTIONS                    //
    //////////////////////////////////////////

    private static function doBlock($action = '')
    {
        // Clear buffered output
        if (!defined('SEMALT_UNIT_TESTING')) self::cls();

        // Take user defined action
        self::blockAction($action);

        // If a human comes by, don't just serve a blank page
        echo sprintf(self::$explanation, self::getHttpReferer(), "https://www.google.com/#q=" . urlencode(preg_replace('/https?:\/\//', '', self::getHttpReferer()) . " referral spam"));
    }

    /**
     * Execute desired action
     *
     * @param string $action
     */
    private static function blockAction($action = '')
    {
        // Redirect or 403
        if (filter_var($action, FILTER_VALIDATE_URL)) {
            self::redirect($action);
        } else {
            self::forbidden();
            if (!empty($action)) echo $action . '<br/>'; // tell them something nice
        }
    }

    private static function cls()
    {
        while (ob_get_level()) ob_end_clean();
    }

    /**
     * @param string $url
     */
    private static function redirect($url)
    {
        header("Location: " . $url);
    }

    /**
     * Returns HTTP Referer if it is available and not empty, null otherwise
     *
     * @return string|null
     */
    private static function getHttpReferer()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
        return null;
    }

    /**
     * @return string
     */
    private static function getBlocklistContents()
    {
        $blocklistContent = file_get_contents(self::getBlocklistFilename());
        return $blocklistContent;
    }

    /**
     * @return string
     */
    private static function getConcatenateBlocklist()
    {
        return self::concatenateBlocklist(self::getBlocklistContents());
    }

    /**
     * @param string $blocklistContent
     * @return array
     */
    private static function parseBlocklist($blocklistContent)
    {
        return array_map('trim', array_filter(explode(PHP_EOL, strtolower($blocklistContent))));
    }

    /**
     * @param string $blocklistContent
     * @return string
     */
    private static function concatenateBlocklist($blocklistContent)
    {
        return self::SEPERATOR . str_replace(PHP_EOL, self::SEPERATOR, strtolower($blocklistContent)) . self::SEPERATOR;
    }
}
