[![Build Status](https://travis-ci.org/nabble/semalt-blocker.svg?branch=master)](https://travis-ci.org/nabble/semalt-blocker)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nabble/semalt-blocker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nabble/semalt-blocker/?branch=master)

semalt-blocker
==============

Block the nasty Semalt botnet from visiting your site and ruining your stats


## composer setup

This is the easiest method, but requires the use of [Composer](http://getcomposer.org). Add semalt-blocker to your project by running the following in your terminal:

```shell
composer require nabble/semalt-blocker:dev-master
```

Then in your project add (but you probably already have this):

```php
require "vendor/autoload.php";
```

## legacy setup

Not using composer? No problem, copy the files `domains` and `combined/semaltblocker.combined.php` to the same directory in your project and add this line:

```php
require "/path/to/semaltblocker.combined.php";
```


## basic usage

It's as easy as:

```php
<?php

\Nabble\Semalt::block();

// ... your app

```

Make sure you add it at the beginning of your code, it will save you!

## options

```php
\Nabble\Semalt::block('http://semalt.com'); // return them their own botnet traffic 
```

## blocked domains

See [domains](https://github.com/nabble/semalt-blocker/blob/master/domains) file. To contribute, please prepare a pull-request or contribute on this public Google Sheets file [a.nabble.nl/semaltdoc](http://a.nabble.nl/semaltdoc).

## licence

MIT