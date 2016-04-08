<?php
use Nabble\SemaltBlocker\Blocker;
use Nabble\SemaltBlocker\Domainparser;

/**
 * @todo: test Blocker::protect() method by using php-test-helpers/php-test-helpers
 */
class SemaltTest extends AbstractSemaltBlockerTest
{
    const INVALID_DOMAIN = '.NotAnUrl?/';

    private $goodReferrals = array(
        'http://www.google.com/?q=query',
        'blog.nabble.nl',
        'https://facebook.com/473289473829/somepage'
    );

    public function testRetrieveDomainlist()
    {
        $domainlist = Blocker::getBlocklist();
        $this->assertTrue(is_array($domainlist), 'Domain list should be an array');
        $this->assertFalse(empty($domainlist), 'Domain list should not be an empty array');
    }

    /**
     * @depends testRetrieveDomainlist
     */
    public function testBlocked()
    {
        $this->mockReferer(null);
        $this->assertFalse(Blocker::blocked(), 'Should not block unset referer');

        $this->mockReferer('');
        $this->assertFalse(Blocker::blocked(), 'Should not block empty referer');

        $this->mockReferer(self::INVALID_DOMAIN);
        $this->assertFalse(Blocker::blocked(), 'Should not block invalid referer');

        $badReferrals = $this->getBadReferrals();
        if (empty($badReferrals)) {
            $this->markTestIncomplete('Could not fetch bad referers for testing');
        }

        foreach($badReferrals as $badReferral) {
            if ($badReferral && substr($badReferral, 0, 1) !== '#') {

                // Referer matches blocked domain exactly
                $this->mockReferer($badReferral);
                $this->assertTrue(Blocker::blocked(), 'Should block bad referer ' . $badReferral);

                // Hostname of referer matches blocked domain exactly
                $this->mockReferer('http://' . $badReferral);
                $this->assertTrue(Blocker::blocked(), 'Should block bad referer http://' . $badReferral);

                // Referer is a subdomain of blocked domain (only on root domains with no path)
                if (($root = Domainparser::getRootDomain($badReferral)) === $badReferral && !trim(Domainparser::getPath($badReferral), '/')) {
                    $this->mockReferer('http://test.' . $badReferral);
                    $this->assertTrue(Blocker::blocked(), 'Should block bad referer http://test.' . $badReferral . ' but it\'s not (' . Blocker::explain(). ')');
                }

                // Referer is a root domain of blocked subdomain
                if ($root !== $badReferral) {
                    $this->mockReferer($root);
                    $this->assertFalse(Blocker::blocked(), 'Should not block root domain ' . $root);
                }
            }
        }

        foreach($this->goodReferrals as $goodReferral) {
            $this->mockReferer($goodReferral);
            $this->assertFalse(Blocker::blocked(), 'Should not block good referer ' . $goodReferral);
        }
    }

    public function testBlockedVerbose()
    {
        $this->mockReferer(null);
        $this->assertEquals('Not blocking because referer header is not set or empty', Blocker::blocked(true), 'Should contain verbose output');

        $this->mockReferer('');
        $this->assertEquals('Not blocking because referer header is not set or empty', Blocker::blocked(true), 'Should contain verbose output');

        $this->mockReferer(self::INVALID_DOMAIN);
        $this->assertEquals('Not blocking because we couldn\'t parse root domain', Blocker::blocked(true), 'Should contain verbose output');

        $this->mockGoodReferer();
        $this->assertContains('Not blocking because referer (', Blocker::blocked(true), 'Should contain verbose output');

        $this->mockBadReferer();
        $this->assertContains('Blocking because referer ', Blocker::blocked(true), 'Should contain verbose output');
    }

    /**
     * @depends testBlocked
     */
    public function testBlock()
    {
        $this->mockGoodReferer();

        ob_start();
        Blocker::protect();
        $output = ob_get_clean();
        $this->assertEmpty($output, 'Shouldn\'t output anything');

        $this->mockBadReferer();

        ob_start();
        Blocker::protect();
        $output = ob_get_clean();
        $explodedExplanation = explode('%s', Blocker::$explanation);
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains($explodedExplanation[0], $output, 'Should contain explanation');

        ob_start();
        Blocker::protect('TEST_MESSAGE');
        $output = ob_get_clean();
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains('TEST_MESSAGE', $output, 'Should contain test message');

        ob_start();
        Blocker::protect('http://www.google.com');
        $output = ob_get_clean();
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        // @todo test headers
    }

    public function testDeprecatedBlock()
    {
        $this->mockBadReferer();

        ob_start();
        \Nabble\Semalt::block('TEST_MESSAGE');
        $output = ob_get_clean();
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains('TEST_MESSAGE', $output, 'Should contain test message');
    }

    private function mockReferer($referer)
    {
        $_SERVER["HTTP_REFERER"] = $referer;
    }

    private function mockGoodReferer()
    {
        $this->mockReferer(current($this->goodReferrals));
    }

    private function mockBadReferer()
    {
        $badReferrals = $this->getBadReferrals();
        // Assuming first bad referral is not a comment
        $this->mockReferer(current($badReferrals));
    }

    private function getBadReferrals()
    {
        return array_map('trim', array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/../domains/blocked'))));
    }
}