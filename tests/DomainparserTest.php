<?php

/**
 * Class DomainparserTest
 */
class DomainparserTest extends PHPUnit_Framework_TestCase
{
    private $rootDomains = array(
        'example.com' => array(
            'http://example.com',
            'https://example.com',
            'ftp://example.com',
            'example.com',
            'example.com//fooled.net',
            'http://example.com/index.html/foo.org',
            'http://test.example.com/index.html/www.fooled.net',
            'http://test1.example.com/fooled.net',
            'http://www.example.com/122/index.html',
            'http://example.com/?foo=bar',
            'http://example.com/?fooled.net',
            'http://example.com/?foo=bar&?bar=foo/url%20with%20spaces/fooled.net/page.html',
        ),
        'example.co.uk' => array(
            'http://example.co.uk',
        ),
        'co.cc' => array(
            'http://example.co.cc',
        )

    );

    public function testRootdomains()
    {
        foreach($this->rootDomains as $root => $examples) {
            foreach($examples as $url) {
                $parser = \Nabble\Domainparser::parseUrl($url);
                $this->assertEquals($root, $parser['topleveldomain']);
            }
        }
    }
}