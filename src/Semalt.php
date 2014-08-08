<?php
namespace Nabble;

/**
 * Class Semalt
 * @package Nabble
 */
class Semalt
{
    public static $blocklist = './../domains/blocked';
    private static $debug = 'Not blocking, no reason given';

    /**
     * Block a page if referer is found on list of blocked domains
     *
     * @param string|bool $redirect Redirects to this URL, or sends 403 response if false
     * @param string|bool $message If set, prints a plaintext message for the bots
     */
    public static function block($redirect = false, $message = null)
    {
        if (self::isRefererOnBlocklist()) {
            // clear buffered output
            self::cls();

            // redirect or deny
            if ($redirect !== false) {
                self::redirect($redirect);
            } else {
                self::forbidden();
            }

            // tell them something nice
            if ($message) {
                echo $message;
            }

            // stop execution altogether, bye bye bots
            exit;
        }
    }

    /**
     * @param bool $verbose
     * @return bool|string
     */
    public static function willBeBlocked($verbose = false)
    {
        $blocked = self::isRefererOnBlocklist();
        if ($verbose === true) {
            return self::$debug;
        }
        return $blocked;
    }

    /**
     * @return array
     */
    public static function getBlocklist()
    {
        return self::parseBlocklist(self::getBlocklistContents());
    }

    private function cls()
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

    private static function forbidden()
    {
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' 403 Forbidden');
    }

    /**
     * @return bool
     */
    private static function isRefererOnBlocklist()
    {
        $referer = self::getHttpReferer();
        if ($referer === false) {
            self::$debug = "Not blocking because referral header is not set or empty";
            return false;
        }
        $rootDomain = self::getRootDomain($referer);
        if ($rootDomain === false) {
            self::$debug = "Not blocking because we couldn't parse referral domain";
            return false;
        }
        if (!in_array($rootDomain, static::getBlocklist())) {
            self::$debug = "Not blocking because referral domain (" . $rootDomain . ") is not found on blocklist";
            return false;
        }
        self::$debug = "Blocking because referral domain (" . $rootDomain . ") is found on blocklist";
        return true;
    }

    /**
     * Extracts root domain from URL if it is available and not empty, returns false otherwise
     *
     * @param string $url
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
        $blocklistContent = file_get_contents(self::getBlocklistFilename());
        return $blocklistContent;
    }

    /**
     * @param string $blocklistContent
     * @return array
     */
    private static function parseBlocklist($blocklistContent)
    {
        return array_map('trim', array_filter(explode(PHP_EOL, $blocklistContent)));
    }
}