<?php

abstract class AbstractSemaltBlockerTest extends PHPUnit_Framework_TestCase
{
    private $backupFilename = 'blocked.backup';
    private $backupPath;
    private $originalPath;

    protected function setUp()
    {
        parent::setUp();

        $this->originalPath = \Nabble\SemaltBlocker\Updater::getBlocklistFilename();
        $this->backupPath = str_replace('blocked', $this->backupFilename, $this->originalPath);
        file_put_contents($this->backupPath, file_get_contents($this->originalPath));
    }

    protected function tearDown()
    {
        parent::tearDown();

        file_put_contents($this->originalPath, file_get_contents($this->backupPath));
        unlink($this->backupPath);
    }

}