[![Latest Stable Version](https://img.shields.io/packagist/v/nabble/semalt-blocker.svg?style=flat-square)](https://packagist.org/packages/nabble/semalt-blocker)
[![Build Status](https://img.shields.io/travis/nabble/semalt-blocker.svg?style=flat-square)](https://travis-ci.org/nabble/semalt-blocker)
[![Coverage Status](https://img.shields.io/coveralls/nabble/semalt-blocker.svg?style=flat-square)](https://coveralls.io/r/nabble/semalt-blocker?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/nabble/semalt-blocker.svg?style=flat-square)](https://scrutinizer-ci.com/g/nabble/semalt-blocker/?branch=master)
[![StyleCI Status](https://styleci.io/repos/22722171/shield)](https://styleci.io/repos/22722171)
[![Dependency Status](https://www.versioneye.com/php/nabble:semalt-blocker/badge.svg?style=flat-square)](https://www.versioneye.com/php/nabble:semalt-blocker)
[![Packagist Downloads](https://img.shields.io/packagist/dt/nabble/semalt-blocker.svg?style=flat-square)](https://packagist.org/packages/nabble/semalt-blocker)
[![License](https://img.shields.io/packagist/l/nabble/semalt-blocker.svg?style=flat-square)](https://packagist.org/packages/nabble/semalt-blocker)

semalt-blocker
==============

### Self-updating PHP library which blocks hundreds of spammy domains from ruining your website statistics

| Bad domains     | Last updated            |
|:---------------:|:-----------------------:|
| 2328 | August 16th, 2016 |

---

Block referral spam with a single line of code. Originally started to stop the nasty Semalt botnet from visiting your site and ruining your stats (of course their domains are still included), the blocklist now contains hundreds of spammy domains. The library will try to self-update every week, so you don't have to worry about `composer update`'s. 

## blocklist

Looking for the blocklist only? No problem:

 - [txt](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked)
 - [json](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked.json)
 - [csv](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked.csv)
 - [xml](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked.xml)
 - [apache](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked.conf)
 - [annotated](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/annotated.json)
 
## sources

The blocklist is compiled from several sources. Currently:

| Source            | Raw source file        | Number of domains         |
|-------------------|------------------------|---------------------------|
| sahava | https://raw.githubusercontent.com/sahava/spam-filter-tool/master/js/spamfilter.js | 417 |
| piwik | https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt | 445 |
| stevie-ray | https://raw.githubusercontent.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/master/generator/domains.txt | 940 |
| antispam | https://raw.githubusercontent.com/antispam/false-referrals/master/false-referrals.txt | 205 |
| ar-communications | https://raw.githubusercontent.com/ARCommunications/Block-Referral-Spam/master/blocker.php | 376 |
| flameeyes | https://raw.githubusercontent.com/Flameeyes/modsec-flameeyes/master/rules/flameeyes_bad_referrers.data | 936 |
| semalt-blocker | ../domains/additional | 1 |
| desbma | https://raw.githubusercontent.com/desbma/referer-spam-domains-blacklist/master/spammers.txt | 1695 |

## debug console

We've added a tool to check whether your site blocks spammy bots. You can find it in the repository and online at: [nabble.nl/semalt](http://nabble.nl/semalt)

## composer setup

This is the easiest method, but requires the use of [Composer](http://getcomposer.org). Add semalt-blocker to your
project by running the following in your terminal:

```shell
composer require nabble/semalt-blocker:~1
```

Then in your project add (but you probably already have this):

```php
require "vendor/autoload.php";
```

## legacy setup

Not using composer? No problem, copy the files `domains/blocked` and `compact/semaltblocker.php` to the same
directory in your project and add this line:

```php
require "/path/to/semaltblocker.php";
```

## basic usage

It's as easy as:

```php
use Nabble\SemaltBlocker\Blocker;

Blocker::protect();

// ... your app
```

Make sure you add it somewhere at the beginning of your code, it will save you!

## self-update

In order for the self-update mechanism to work, make sure the `domains/blocked` file is writable by the webserver:

```bash
$ chmod a+w vendor/nabble/semalt-blocker/domains/blocked
```

## options 

```php
Blocker::protect(); // default, serve a 403 Forbidden response
Blocker::protect('http://semalt.com'); // return them their own botnet traffic
Blocker::protect('Hi, bot'); // displays a nice message when blocked
```

_All calls to the `protect` function will trigger the auto-updater at a regular interval._

If you want to stay in control even more, use this:

```php
$blocked = Blocker::blocked(); // returns true when a blocked referrer is detected
```

If you want an explanation for why a referer is blocked, use: 

```php
echo Blocker::explain();
```

The self-updater runs every 7 days by default. To force updating the domain list, use this:

```php
use Nabble\SemaltBlocker\Updater;

Updater::update(true);
```

## contribute

Yes, please! Feel free to open issues or pull-requests.

## licence

MIT
