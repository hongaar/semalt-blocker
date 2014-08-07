[![Build Status](https://travis-ci.org/nabble/semalt-blocker.svg?branch=master)](https://travis-ci.org/nabble/semalt-blocker)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nabble/semalt-blocker/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nabble/semalt-blocker/?branch=master)

semalt-blocker
==============

Block the nasty Semalt botnet from visiting your site and ruining your stats


## setup

Add semalt-blocker to your project by running the following in your terminal:

```shell
composer require nabble/semalt-blocker:dev-master
```

This requires the use of [Composer](http://getcomposer.org).

## basic usage

It's as easy as:

```php
<?php

require "vendor/autoload.php";
\Nabble\Semalt::block();

// ... your app

```

## options

```php
\Nabble\Semalt::block('http://semalt.com'); // return them their own botnet traffic 
```

## blocked domains

See [domains](https://github.com/nabble/semalt-blocker/blob/master/domains) file. To contribute, please prepare a pull-request or contribute on this public Google Sheets file [a.nabble.nl/semaltdoc](http://a.nabble.nl/semaltdoc).

## licence

MIT