<html>
    <head>
        <title>semalt blocker debug console</title>
        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
        <style>
            section {
                margin: 100px 0;
            }
            form {
                margin: 0 0 50px;
            }
            input, button {
                padding: 10px;
                font-size: 18px;
            }
            span {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <section class="container">
            <div class="col-md-10 text-center">

                <h1>Semalt blocker debug console</h1>

                <form method="get">
                    <?php
                    $url = isset($_GET['url']) ?
                        $_GET['url'] :
                        '';
//                        "http://" . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['REQUEST_URI']) . 'target.php';
                    ?>
                    <input type="url" name="url" placeholder="your website url" size="50" value="<?php echo $url; ?>" />
                    <button type="submit">debug url</button>
                </form>

                <?php if (isset($_GET['url']) && $_GET['url']) {

                    function status($code)
                    {
                        if (substr($code, 0, 1) == '2') return '<span style="color: red;">' . $code . '</span>';
                        if (substr($code, 0, 1) == '3') return '<span style="color: orange;">' . $code . '</span>';
                        return '<span style="color: green;">' . $code . '</span>';
                    }

                    ob_implicit_flush(true);
                    ob_end_flush();

                    require '../vendor/autoload.php';

                    $url = $_GET['url'];

                    $list = \Nabble\Semalt::getBlocklist();
                    $client = new \Guzzle\Http\Client();

                    foreach($list as $referral) {

                        $request = $client->get($url, [
                            'Referer' => 'http://' . $referral
                        ]);

                        try {
                            $response = $request->send();
                        } catch (Guzzle\Http\Exception\BadResponseException $e) {
                            $response = $e->getResponse();
                        }

                        echo $referral . ': ' . status($response->getStatusCode()) . '<br/>';

                    }

                } ?>

            </div>
        </section>

    </body>
</html>