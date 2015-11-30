<?php

use Nabble\SemaltBlocker\Domainparser;

class DomainparserTest extends AbstractSemaltBlockerTest
{
    private $hostnames = array(
        'google.com' => array(
            'http://google.com',
            'https://google.com',
            'ftp://google.com',
            'google.com',
            'http://GOOGLE.COM',
            'google.com//fooled.net',
            'http://google.com/index.html/foo.org',
            'http://google.com/?foo=bar&?bar=foo/url%20with%20spaces/fooled.net/page.html',
        ),
        'test.google.com' => array(
            'http://test.google.com/index.html/www.fooled.net',
            'http://test.google.com/fooled.net',
            'http://test.google.com/122/index.html',
            'http://test.google.com/?foo=bar',
            'http://test.google.com/?fooled.net',
        ),
        'google.co.uk' => array(
            'http://google.CO.uk',
        ),
        'co.uk' => array(
            'http://co.uk'
        ),
        'semalt.semalt.com' => array(
            'http://semalt.semalt.com/crawler.php?u=http://my.site.com'
        ),
        'google.co.cc' => array(
            'http://google.co.cc',
        ),
        'xn--80adgcaax6acohn6r.xn--p1ai' => array(
            'непереводимая.рф',
            'http://непереводимая.рф/test',
            'xn--80adgcaax6acohn6r.xn--p1ai',
            'http://xn--80adgcaax6acohn6r.xn--p1ai/test'
        )
    );

    private $rootDomains = array(
        'google.com' => array(
            'http://google.com',
            'https://google.com',
            'ftp://google.com',
            'google.com',
            'http://GOOGLE.COM',
            'google.com//fooled.net',
            'http://google.com/index.html/foo.org',
            'http://test.google.com/index.html/www.fooled.net',
            'http://test1.google.com/fooled.net',
            'http://www.google.com/122/index.html',
            'http://google.com/?foo=bar',
            'http://google.com/?fooled.net',
            'http://google.com/?foo=bar&?bar=foo/url%20with%20spaces/fooled.net/page.html',
        ),
        'google.co.uk' => array(
            'http://google.co.uk',
        ),
        'co.uk' => array(
            'http://co.uk'
        ),
        'semalt.com' => array(
            'http://semalt.semalt.com/crawler.php?u=http://my.site.com'
        ),
        'co.cc' => array(
            'http://google.co.cc',
        ),
        'xn--80adgcaax6acohn6r.xn--p1ai' => array(
            'непереводимая.рф',
            'http://непереводимая.рф/test',
            'xn--80adgcaax6acohn6r.xn--p1ai',
            'http://xn--80adgcaax6acohn6r.xn--p1ai/test'
        )
    );

    private $invalidDomains = array(
        '.hallo?/',
        'http://uk',
        'com'
    );

    public function testHostnames()
    {
        foreach($this->hostnames as $expectedHost => $samples) {
            foreach($samples as $url) {
                $parsedHostname = Domainparser::getHostname($url);
                $this->assertNotFalse($parsedHostname, 'Parsed hostname for ' . $url . ' should not be false');
                $this->assertEquals($expectedHost, $parsedHostname, 'Expected hostname ' . $expectedHost . ', got ' . $parsedHostname . ' instead.');
            }
        }
    }

    public function testRootDomains()
    {
        foreach($this->rootDomains as $expectedRoot => $samples) {
            foreach($samples as $url) {
                $parsedRootDomain = Domainparser::getRootDomain($url);
                $this->assertNotFalse($parsedRootDomain, 'Parsed root domain for ' . $url . ' should not be false');
                $this->assertEquals($expectedRoot, $parsedRootDomain, 'Expected root ' . $expectedRoot . ', got ' . $parsedRootDomain . ' instead.');
            }
        }
    }

    public function testInvalidDomains()
    {
        foreach($this->invalidDomains as $url) {
            $parsedRootDomain = Domainparser::getRootDomain($url);
            $this->assertEquals(false, $parsedRootDomain);
        }
    }
}