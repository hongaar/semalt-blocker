<?php

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
        $domainlist = \Nabble\SemaltBlocker\Blocker::getBlocklist();
        $this->assertTrue(is_array($domainlist), 'Domain list should be an array');
        $this->assertFalse(empty($domainlist), 'Domain list should not be an empty array');
    }

    /**
     * @depends testRetrieveDomainlist
     */
    public function testBlocked()
    {
        $this->mockReferer(null);
        $this->assertFalse(\Nabble\SemaltBlocker\Blocker::blocked(), 'Should not block unset referral');

        $this->mockReferer('');
        $this->assertFalse(\Nabble\SemaltBlocker\Blocker::blocked(), 'Should not block empty referral');

        $this->mockReferer(self::INVALID_DOMAIN);
        $this->assertFalse(\Nabble\SemaltBlocker\Blocker::blocked(), 'Should not block invalid referral');

        $badReferrals = $this->getBadReferrals();
        if (empty($badReferrals)) {
            $this->markTestIncomplete('Could not fetch bad referrals for testing');
        }

        foreach($badReferrals as $badReferral) {
            if ($badReferral && substr($badReferral, 0, 1) !== '#') {
                $this->mockReferer($badReferral);
                $this->assertTrue(\Nabble\SemaltBlocker\Blocker::blocked(), 'Should block bad referral ' . $badReferral);
            }
        }

        foreach($this->goodReferrals as $goodReferral) {
            $this->mockReferer($goodReferral);
            $this->assertFalse(\Nabble\SemaltBlocker\Blocker::blocked(), 'Should not block good referral ' . $goodReferral);
        }
    }

    public function testBlockedVerbose()
    {
        $this->mockReferer(null);
        $this->assertEquals('Not blocking because referral header is not set or empty', \Nabble\SemaltBlocker\Blocker::blocked(true), 'Should contain verbose output');

        $this->mockReferer('');
        $this->assertEquals('Not blocking because referral header is not set or empty', \Nabble\SemaltBlocker\Blocker::blocked(true), 'Should contain verbose output');

        $this->mockReferer(self::INVALID_DOMAIN);
        $this->assertEquals('Not blocking because we couldn\'t parse referral domain', \Nabble\SemaltBlocker\Blocker::blocked(true), 'Should contain verbose output');

        $this->mockGoodReferer();
        $this->assertContains('Not blocking because referral domain (', \Nabble\SemaltBlocker\Blocker::blocked(true), 'Should contain verbose output');

        $this->mockBadReferer();
        $this->assertContains('Blocking because referral domain (', \Nabble\SemaltBlocker\Blocker::blocked(true), 'Should contain verbose output');
    }

    public function testGetReason()
    {
        // Can't test that reason is a default, as previous tests have already set it

        // Set the reason to something else
        $this->mockReferer('');
        $this->assertFalse(\Nabble\SemaltBlocker\Blocker::isRefererOnBlocklist());
        $this->assertEquals('Not blocking because referral header is not set or empty', \Nabble\SemaltBlocker\Blocker::getReason(), 'Should be empty reason');
        $this->assertNotContains('Blocking because referral domain (', \Nabble\SemaltBlocker\Blocker::getReason());

        // Now prove it changes with a bad referrer
        $this->mockBadReferer();
        $this->assertTrue(\Nabble\SemaltBlocker\Blocker::isRefererOnBlocklist());
        $this->assertContains('Blocking because referral domain (', \Nabble\SemaltBlocker\Blocker::getReason(), 'reason should have a value');
    }

    /**
     * @depends testBlocked
     */
    public function testBlock()
    {
        $this->mockGoodReferer();

        ob_start();
        \Nabble\SemaltBlocker\Blocker::protect();
        $output = ob_get_clean();
        $this->assertEmpty($output, 'Shouldn\'t output anything');

        $this->mockBadReferer();

        ob_start();
        \Nabble\SemaltBlocker\Blocker::protect();
        $output = ob_get_clean();
        $explodedExplanation = explode('%s', \Nabble\SemaltBlocker\Blocker::$explanation);
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains($explodedExplanation[0], $output, 'Should contain explanation');

        ob_start();
        \Nabble\SemaltBlocker\Blocker::protect('TEST_MESSAGE');
        $output = ob_get_clean();
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains('TEST_MESSAGE', $output, 'Should contain test message');

        ob_start();
        \Nabble\SemaltBlocker\Blocker::protect('http://www.google.com');
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
