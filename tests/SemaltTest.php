<?php

/**
 * Class SemaltTest
 *
 * @todo: test Semalt::block() method by using php-test-helpers/php-test-helpers
 */
class SemaltTest extends PHPUnit_Framework_TestCase
{
    private $goodReferrals = array(
        'http://www.google.com/?q=query',
        'blog.nabble.nl',
        'https://facebook.com/473289473829/somepage'
    );

    public function testRetrieveDomainlist()
    {
        $domainlist = \Nabble\Semalt::getBlocklist();
        $this->assertTrue(is_array($domainlist), 'Domain list should be an array');
        $this->assertFalse(empty($domainlist), 'Domain list should not be an empty array');
    }

    /**
     * @depends testRetrieveDomainlist
     */
    public function testBlock()
    {
        $this->mockReferer(null);
        $this->assertFalse(\Nabble\Semalt::blocked(), 'Should not block unset referral');

        $this->mockReferer('');
        $this->assertFalse(\Nabble\Semalt::blocked(), 'Should not block empty referral');

        $this->mockReferer('NotAnUrl');
        $this->assertFalse(\Nabble\Semalt::blocked(), 'Should not block invalid referral');

        $badReferrals = $this->getBadReferrals();
        if (empty($badReferrals)) {
            $this->markTestIncomplete('Could not fetch bad referrals for testing');
        }

        foreach($badReferrals as $badReferral) {
            if ($badReferral && substr($badReferral, 0, 1) !== '#') {
                $this->mockReferer($badReferral);
                $this->assertTrue(\Nabble\Semalt::blocked(), 'Should block bad referral ' . $badReferral);
            }
        }

        foreach($this->goodReferrals as $goodReferral) {
            $this->mockReferer($goodReferral);
            $this->assertFalse(\Nabble\Semalt::blocked(), 'Should not block good referral ' . $goodReferral);
        }
    }

    private function mockReferer($referer)
    {
        $_SERVER["HTTP_REFERER"] = $referer;
    }

    private function getBadReferrals()
    {
        return array_map('trim', array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/../domains/referrals'))));
    }
}