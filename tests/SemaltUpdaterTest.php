<?php

class SemaltUpdaterTest extends PHPUnit_Framework_TestCase
{
    private $blockFile;
    private $domainUrl;

    public function setUp()
    {
        $this->blockFile = (\Nabble\SemaltUpdater::$blocklist = './domains/blocked');
        $this->domainUrl = \Nabble\SemaltUpdater::$updateUrl;
    }

    public function testDomainsRetrieval()
    {
        $domainList = \Nabble\SemaltUpdater::getNewDomainList();
        $this->assertNotEmpty(trim($domainList), 'Domain list shouldn\'t be empty');
    }

    /**
     * @depends testDomainsRetrieval
     */
    public function testDomainsUpdate()
    {
        $domainList = \Nabble\SemaltUpdater::getNewDomainList();
        $ttl = \Nabble\SemaltUpdater::$ttl;

        file_put_contents($this->blockFile, '');
        \Nabble\SemaltUpdater::$ttl = -60;
        \Nabble\SemaltUpdater::update();
        $this->assertStringEqualsFile($this->blockFile, $domainList, 'Blocked file should match online domain list');

        file_put_contents($this->blockFile, '');
        \Nabble\SemaltUpdater::$ttl = $ttl;
        \Nabble\SemaltUpdater::update();
        $this->assertStringEqualsFile($this->blockFile, '', 'Blocked file should not be updated');

        file_put_contents($this->blockFile, $domainList);
    }
}