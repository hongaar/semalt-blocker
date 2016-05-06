<?php

namespace Nabble\SemaltBlocker;

/**
 * The `update` method is called from the Blocker class every week to grab latest domain list from GitHub.
 */
class Updater
{
    public static $ttl = 604800; // = 60 * 60 * 24 * 7 = 7 days
    public static $updateUrl = 'https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked';

    private static $blocklist = './../../domains/blocked';

    //////////////////////////////////////////
    // PUBLIC API                           //
    //////////////////////////////////////////

    /**
     * Try to update the blocked domains list.
     *
     * @param bool $force
     */
    public static function update($force = false)
    {
        if (!defined('SEMALT_UNIT_TESTING') && !self::isWritable()) {
            return;
        }

        if (!$force && !self::isOutdated()) {
            return;
        }

        self::doUpdate();
    }

    /**
     * @return string
     */
    public static function getNewDomainList()
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL            => self::$updateUrl,
            ]);
            $domains = curl_exec($curl);
            curl_close($curl);
        } else {
            $domains = @file_get_contents(self::$updateUrl);
        }

        return $domains;
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

    private static function doUpdate()
    {
        $domains = self::getNewDomainList();

        // Don't panic if updating the file throws an error of some kind
        if (trim($domains) !== '') {
            @file_put_contents(self::getBlocklistFilename(), $domains);
        }
    }

    /**
     * @return bool
     */
    private static function isWritable()
    {
        return is_writable(self::getBlocklistFilename());
    }

    /**
     * @return bool
     */
    private static function isOutdated()
    {
        return filemtime(self::getBlocklistFilename()) < (time() - self::$ttl);
    }
}
