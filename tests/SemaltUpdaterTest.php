<?php

class SemaltUpdaterTest extends AbstractSemaltBlockerTest
{
    private $blockFile;
    private $domainUrl;

    public function setUp()
    {
        parent::setUp();

        $this->blockFile = \Nabble\SemaltBlocker\Updater::getBlocklistFilename();
        $this->domainUrl = \Nabble\SemaltBlocker\Updater::$updateUrl;
    }

    public function testDomainsRetrieval()
    {
        $domainList = \Nabble\SemaltBlocker\Updater::getNewDomainList();
        $this->assertNotEmpty(trim($domainList), 'Domain list shouldn\'t be empty');
    }

    /**
     * @depends testDomainsRetrieval
     */
    public function testDomainsUpdate()
    {
        $domainList = \Nabble\SemaltBlocker\Updater::getNewDomainList();
        $ttl = \Nabble\SemaltBlocker\Updater::$ttl;

        file_put_contents($this->blockFile, '');
        \Nabble\SemaltBlocker\Updater::$ttl = -60;
        \Nabble\SemaltBlocker\Updater::update();
        $this->assertStringEqualsFile($this->blockFile, $domainList, 'Blocked file should match online domain list');

        file_put_contents($this->blockFile, '');
        \Nabble\SemaltBlocker\Updater::$ttl = $ttl;
        \Nabble\SemaltBlocker\Updater::update();
        $this->assertStringEqualsFile($this->blockFile, '', 'Blocked file should not be updated');

        file_put_contents($this->blockFile, $domainList);
    }

    /**
     * @depends testDomainsRetrieval
     */
    public function testForcedDomainsUpdate()
    {
        $domainList = \Nabble\SemaltBlocker\Updater::getNewDomainList();

        file_put_contents($this->blockFile, '');
        \Nabble\SemaltBlocker\Updater::$ttl = 60 * 60 * 24 * 9999; // = 9999 days;
        \Nabble\SemaltBlocker\Updater::update(true);
        $this->assertStringEqualsFile($this->blockFile, $domainList, 'Blocked file should match online domain list');
    }
}