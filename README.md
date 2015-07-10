[![Latest Stable Version](https://poser.pugx.org/nabble/semalt-blocker/v/stable)](https://packagist.org/packages/nabble/semalt-blocker)
[![Build Status](https://travis-ci.org/nabble/semalt-blocker.svg?branch=master)](https://travis-ci.org/nabble/semalt-blocker)
[![Coverage Status](https://coveralls.io/repos/nabble/semalt-blocker/badge.svg?branch=master)](https://coveralls.io/r/nabble/semalt-blocker?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nabble/semalt-blocker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nabble/semalt-blocker/?branch=master)
[![License](https://poser.pugx.org/nabble/semalt-blocker/license)](https://packagist.org/packages/nabble/semalt-blocker)

semalt-blocker
==============

Block referral spam with a single line of code. Originally started to stop the nasty Semalt botnet from visiting your site and ruining your stats (their domains are included by default), but you can use it to block any number of spammy domains. The script will try to self-update every week, so you don't have to worry about `composer update`'s. 

## sources

The blocklist is compiled from several sources. Currently:

 - [piwik/referrer-spam-blacklist](https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt)
 - [lonegoatuk.tumblr.com](http://lonegoatuk.tumblr.com/post/107307494431/google-analytics-referral-spambot-list)
 - [Stevie-Ray/htaccess-referral-spam-blacklist-block](https://raw.githubusercontent.com/Stevie-Ray/htaccess-referral-spam-blacklist-block/master/.htaccess)
 - [antispam/false-referrals](https://raw.githubusercontent.com/antispam/false-referrals/master/false-referrals.txt)
 
## blocklist

Looking for the blocklist only? [Here's the latest raw text file](https://raw.githubusercontent.com/nabble/semalt-blocker/master/domains/blocked)

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
<?php

\Nabble\Semalt::block();

// ... your app

```

Make sure you add it at the beginning of your code, it will save you!

## self-update

In order for the self-update mechanism to work, make sure the `domains/blocked` file is writable by the webserver:

```
chmod 777 domains/blocked
```

## options

```php
\Nabble\Semalt::block(); // default, serve a 403 Forbidden response
\Nabble\Semalt::block('http://semalt.com'); // return them their own botnet traffic
\Nabble\Semalt::block('Hi, nasty bot'); // displays a nice message when blocked
```

If you want to stay in control even more, use this:

```php
$blocked = \Nabble\Semalt::blocked(); // returns true when a blocked referrer is detected
```

## contribute

Yes, please!

Contribute by adding your list of known referral URL's used by Semalt in the file
[referrals](https://github.com/nabble/semalt-blocker/blob/master/domains/referrals) which is used in the unit tests.

As new referral URL's get added, the [blocked](https://github.com/nabble/semalt-blocker/blob/master/domains/blocked) file needs updating
until all tests pass. This way we make sure new referrals are being blocked.

If you want to help, please prepare a pull-request or contribute on this public Google Sheets file
[a.nabble.nl/semaltdoc](http://a.nabble.nl/semaltdoc).

## licence

MIT
