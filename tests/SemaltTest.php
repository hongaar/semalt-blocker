<?php

/**
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
    public function testBlocked()
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

    /**
     * @depends testBlocked
     */
    public function testBlock()
    {
        $this->mockGoodReferer();

        ob_start();
        $goodReferer = \Nabble\Semalt::block();
        $output = ob_get_clean();
        $this->assertNull($goodReferer, 'Shouldn\'t return anything');
        $this->assertEmpty($output, 'Shouldn\'t output anything');

        $this->mockBadReferer();

        ob_start();
        $withoutAction = \Nabble\Semalt::block();
        $output = ob_get_clean();
        $explodedExplanation = explode('%s', \Nabble\Semalt::$explanation);
        $this->assertNull($withoutAction, 'Shouldn\'t return anything');
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains($explodedExplanation[0], $output, 'Should contain explanation');

        ob_start();
        $withMessage = \Nabble\Semalt::block('TEST_MESSAGE');
        $output = ob_get_clean();
        $this->assertNull($withMessage, 'Shouldn\'t return anything');
        $this->assertNotNull($output, 'Output shouldn\'t be null');
        $this->assertContains('TEST_MESSAGE', $output, 'Should contain test message');

        // @todo test headers
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
        return array_map('trim', array_filter(explode(PHP_EOL, file_get_contents(__DIR__ . '/../domains/referrals'))));
    }
}