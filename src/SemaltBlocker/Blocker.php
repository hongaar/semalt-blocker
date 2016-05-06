<?php

namespace Nabble\SemaltBlocker;

/**
 * The most important class for this package. Basic usage:
 * ```
 * Blocker::protect();
 * ```.
 */
class Blocker
{
    const SEPERATOR = ':';

    public static $explanation = "Access to this website has been blocked because your referral is set to %s. <a href='%s'>Read why</a>";

    private static $blocklist = './../../domains/blocked';
    private static $reason = 'Not blocking, no reason given';

    //////////////////////////////////////////
    // PUBLIC API                           //
    //////////////////////////////////////////

    /**
     * Block a page if referer is found on list of blocked domains.
     *
     * @param string $action If empty, send 403 response; if URL, redirect here; if non-empty string, print message
     */
    public static function protect($action = '')
    {
        // Try to update the list
        if (!defined('SEMALT_UNIT_TESTING')) {
            Updater::update();
        }

        // Simply stop here if referer is not on the list
        if (!self::isRefererOnBlocklist()) {
            return;
        }

        self::doBlock($action);

        // Stop execution altogether, bye bye bots
        if (!defined('SEMALT_UNIT_TESTING')) {
            exit;
        }
    }

    /**
     * @param bool $verbose Deprecated. Please use the explain() method instead.
     *
     * @return bool|string
     */
    public static function blocked($verbose = false)
    {
        $blocked = self::isRefererOnBlocklist();
        if ($verbose === true) {
            return self::$reason;
        }

        return $blocked;
    }

    /**
     * @return string
     */
    public static function explain()
    {
        return self::$reason;
    }

    /**
     * Send a 403 Forbidden header.
     */
    public static function forbidden()
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' 403 Forbidden');
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

    /**
     * Responsible for sending action output.
     *
     * @param string $action
     */
    private static function doBlock($action = '')
    {
        // Clear buffered output
        if (!defined('SEMALT_UNIT_TESTING')) {
            self::cls();
        }

        // Take user defined action
        self::blockAction($action);

        // If a human comes by, don't just serve a blank page
        echo sprintf(self::$explanation, self::getHttpReferer(), 'https://www.google.com/#q=' . urlencode(preg_replace('/https?:\/\//', '', self::getHttpReferer()) . ' referral spam'));
    }

    /**
     * Execute desired action.
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
            if (!empty($action)) {
                echo $action . '<br/>';
            } // tell them something nice
        }
    }

    /**
     * Clear output buffer.
     */
    private static function cls()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * Redirect to a url by sending the appropriate header.
     *
     * @param string $url
     */
    private static function redirect($url)
    {
        header('Location: ' . $url);
    }

    /**
     * The public use of this function is undocumented.
     *
     * @return bool
     */
    public static function isRefererOnBlocklist()
    {
        $referer = self::getHttpReferer();
        if ($referer === null) {
            self::$reason = 'Not blocking because referer header is not set or empty';

            return false;
        }

        return self::isUrlOnBlocklist($referer, 'referer');
    }

    /**
     * The public use of this function is undocumented.
     *
     * @param string $url
     * @param string $entity
     *
     * @return bool
     */
    public static function isUrlOnBlocklist($url, $entity = 'url')
    {
        $rootDomain = Domainparser::getRootDomain($url);
        if ($rootDomain === false) {
            self::$reason = "Not blocking because we couldn't parse root domain";

            return false;
        }

        $blocklist = self::getConcatenateBlocklist();
        if (substr_count($blocklist, self::SEPERATOR . $rootDomain . self::SEPERATOR)) {
            self::$reason = 'Blocking because ' . $entity . ' root domain (' . $rootDomain . ') is found on blocklist';

            return true;
        }

        $hostname = Domainparser::getHostname($url);
        if (substr_count($blocklist, self::SEPERATOR . $hostname . self::SEPERATOR)) {
            self::$reason = 'Blocking because ' . $entity . ' hostname (' . $hostname . ') is found on blocklist';

            return true;
        }

        $path = Domainparser::getPath($url);
        if (trim($path, '/')) {
            if (substr_count($blocklist, self::SEPERATOR . $rootDomain . $path . self::SEPERATOR)) {
                self::$reason = 'Blocking because ' . $entity . ' root domain/path (' . $rootDomain . $path . ') is found on blocklist';

                return true;
            }
            if (substr_count($blocklist, self::SEPERATOR . $hostname . $path . self::SEPERATOR)) {
                self::$reason = 'Blocking because ' . $entity . ' hostname/path (' . $hostname . $path . ') is found on blocklist';

                return true;
            }
        }

        self::$reason = 'Not blocking because ' . $entity . ' (' . $url . ') is not matched against blocklist';

        return false;
    }

    /**
     * Returns HTTP Referer if it is available and not empty, null otherwise.
     *
     * @return string|null
     */
    private static function getHttpReferer()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
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
     *
     * @return array
     */
    private static function parseBlocklist($blocklistContent)
    {
        return array_map('trim', array_filter(explode(PHP_EOL, strtolower($blocklistContent))));
    }

    /**
     * @param string $blocklistContent
     *
     * @return string
     */
    private static function concatenateBlocklist($blocklistContent)
    {
        return self::SEPERATOR . str_replace(PHP_EOL, self::SEPERATOR, strtolower($blocklistContent)) . self::SEPERATOR;
    }
}
