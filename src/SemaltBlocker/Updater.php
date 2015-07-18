<?php
namespace Nabble\SemaltBlocker;

/**
 * Class Updater
 * @package Nabble
 */
class Updater
{
    public static $blocklist = './../../domains/blocked';
    public static $ttl = 604800; // = 60 * 60 * 24 * 7 = 7 days
    public static $updateUrl = 'https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked';

    //////////////////////////////////////////
    // PUBLIC API                           //
    //////////////////////////////////////////

    /**
     * Try to update the blocked domains list
     */
    public static function update()
    {
        if (!defined('SEMALT_UNIT_TESTING') && !self::isWritable()) return;

        if (!self::isOutdated()) return;

        self::doUpdate();
    }

    public static function getNewDomainList()
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => self::$updateUrl
            ));
            $domains = curl_exec($curl);
            curl_close($curl);
        } else {
            $domains = @file_get_contents(self::$updateUrl);
        }
        return $domains;
    }

    //////////////////////////////////////////
    // PRIVATE FUNCTIONS                    //
    //////////////////////////////////////////

    private static function doUpdate()
    {
        $domains = self::getNewDomainList();

        // Don't panic if updating the file throws an error of some kind
        if (trim($domains) !== '')
            @file_put_contents(self::$blocklist, $domains);
    }

    private static function isWritable()
    {
        return is_writable(self::$blocklist);
    }

    private static function isOutdated()
    {
        return filemtime(self::$blocklist) < (time() - self::$ttl);
    }
}