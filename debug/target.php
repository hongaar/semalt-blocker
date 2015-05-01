<?php
require '../vendor/autoload.php';
if (\Nabble\Semalt::blocked()) {
    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
    header($protocol . ' 403 Forbidden');
}
?>
<html>
    <head>
        <title>semalt blocker test target</title>
    </head>
    <body>
        <?php echo \Nabble\Semalt::blocked(true); ?>

    </body>
</html>