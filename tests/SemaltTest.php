<?php
class SemaltTest extends PHPUnit_Framework_TestCase
{
    private $badReferrals = array(
        'http://semalt.semalt.com/crawler.php?u=http://my.site.com',
        'http://musicas.kambasoft.com',
        'http://semalt.com/account/or_whatever?id=42789sdf',
        'kambasoft.com'
    );

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
        $this->assertFalse(\Nabble\Semalt::willBeBlocked(), 'Should not block unset referral');

        $this->mockReferer('');
        $this->assertFalse(\Nabble\Semalt::willBeBlocked(), 'Should not block empty referral');

        $this->mockReferer('NotAnUrl');
        $this->assertFalse(\Nabble\Semalt::willBeBlocked(), 'Should not block invalid referral');

        foreach($this->badReferrals as $badReferral) {
            $this->mockReferer($badReferral);
            $this->assertTrue(\Nabble\Semalt::willBeBlocked(), 'Should block bad referral ' . $badReferral);
        }

        foreach($this->goodReferrals as $goodReferral) {
            $this->mockReferer($goodReferral);
            $this->assertFalse(\Nabble\Semalt::willBeBlocked(), 'Should not block good referral ' . $goodReferral);
        }
    }

    private function mockReferer($referer)
    {
        $_SERVER["HTTP_REFERER"] = $referer;
    }
}