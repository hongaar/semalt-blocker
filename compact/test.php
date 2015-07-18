<?php

copy('./../domains/blocked', './../compact/blocked');

include 'semaltblocker.php';

$_SERVER["HTTP_REFERER"] = 'http://semalt.com';
var_dump(\Nabble\SemaltBlocker\Blocker::blocked(true));

unlink('./../compact/blocked');

