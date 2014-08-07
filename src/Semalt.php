<?php
namespace Nabble;

/**
 * Class Semalt
 * @package Nabble
 */
class Semalt
{
    private static $blocklist = './../domains';
    private static $debug = 'Not blocking, no reason given';

    /**
     *
     */
    public static function block($redirect = false)
    {
        if (static::isRefererOnBlocklist()) {
            // redirect
            if ($redirect !== false) {
                header("Location: " . $redirect);
            }
            // exit
            exit;
        }
    }

    /**
     * @param bool $verbose
     * @return bool|string
     */
    public static function willBeBlocked($verbose = false)
    {
        $blocked = static::isRefererOnBlocklist();
        if ($verbose === true) {
            return static::$debug;
        }
        return $blocked;
    }

    /**
     * @return array
     */
    public static function getBlocklist()
    {
        return static::parseBlocklist(static::getBlocklistContents());
    }

    /**
     * @return bool
     */
    private static function isRefererOnBlocklist()
    {
        $referer = static::getHttpReferer();
        if ($referer === false) {
            static::$debug = "Not blocking because referral header is not set or empty";
            return false;
        }
        $rootDomain = static::getRootDomain($referer);
        if ($rootDomain === false) {
            static::$debug = "Not blocking because we couldn't parse referral domain";
            return false;
        }
        if (!in_array($rootDomain, static::getBlocklist())) {
            static::$debug = "Not blocking because referral domain (" . $rootDomain . ") is not found on blocklist";
            return false;
        }
        static::$debug = "Blocking because referral domain (" . $rootDomain . ") is found on blocklist";
        return true;
    }

    /**
     * Extracts root domain from URL if it is available and not empty, returns false otherwise
     *
     * @param $url
     * @return string|bool
     */
    private static function getRootDomain($url)
    {
        $urlParts = Domainparser::parseUrl($url);
        return (isset($urlParts['topleveldomain']) && !empty($urlParts['topleveldomain'])) ? $urlParts['topleveldomain'] : false;
    }

    /**
     * Returns HTTP Referer if it is available and not empty, false otherwise
     *
     * @return string|bool
     */
    private static function getHttpReferer()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
        return false;
    }

    /**
     * @return string
     */
    private static function getBlocklistFilename()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . static::$blocklist;
    }

    /**
     * @return string
     */
    private static function getBlocklistContents()
    {
        $blocklistContent = file_get_contents(static::getBlocklistFilename());
        return $blocklistContent;
    }

    /**
     * @param $blocklistContent
     * @return array
     */
    private static function parseBlocklist($blocklistContent)
    {
        return array_map('trim', array_filter(explode(PHP_EOL, $blocklistContent)));
    }
}