<?php
require '../vendor/autoload.php';
\Nabble\Semalt::block();
?>
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
                max-width: 100%;
            }
            input[type=url] {
                width: 30em;
            }
            .table {
                width: auto;
                margin: 0 auto;
            }
            span {
                font-weight: bold;
            }
            footer > div {
                padding-top: 50px;
                margin-bottom: 50px;
                border-top: 1px solid silver;
            }
        </style>
    </head>

    <body>
        <section class="container">
            <div class="col-md-12 text-center">

                <h1>Semalt blocker debug console</h1>

                <form method="get">
                    <?php
                    $url = isset($_GET['url']) ?
                        $_GET['url'] :
                        '';
//                        "http://" . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['REQUEST_URI']) . 'target.php';
                    ?>
                    <input type="url" name="url" placeholder="your website url" value="<?php echo $url; ?>" />
                    <button type="submit">debug url</button>
                </form>

                <?php if (isset($_GET['url']) && empty($_GET['url'])) {

                    echo 'No URL provided';

                } else if (isset($_GET['url']) && $_GET['url']) {

                    function status($code, $redirect = false)
                    {
                        if (substr($code, 0, 1) == '2') return '<span style="color: red;">Not blocked</span>';
                        if (substr($code, 0, 1) == '3') return '<span style="color: orange;">Redirect </span> &rarr; ' . $redirect;
                        return '<span style="color: green;">Blocked</span>';
                    }

                    ob_implicit_flush(true);
                    ob_end_flush();

                    $list = [];
                    $url = filter_var($_GET['url'], FILTER_VALIDATE_URL);

                    if ($url) {

                        $list = \Nabble\Semalt::getBlocklist();
                        $client = new \Guzzle\Http\Client(null, array('redirect.disable' => true));

                    }

                    echo "<p class='status'><em>Working...</em></p>";
                    echo "<table class='table table-bordered table-condensed table-hover'>";

                    foreach($list as $referral) {

                        $request = $client->get($url, [
                            'Referer' => 'http://' . $referral
                        ]);

                        $redirect = false;
                        try {
                            $response = $request->send();
                            if ($response->getStatusCode() == 302 || $response->getStatusCode() == 301) {
                                $redirect = (string) $response->getHeader('Location');
                            }
                        } catch (Guzzle\Http\Exception\BadResponseException $e) {
                            $response = $e->getResponse();
                        } catch (Exception $e) {
                            $response = false;
                        }

                        if ($response) echo "<tr><th>" . $referral . '</th><td>' . status($response->getStatusCode(), $redirect) . '</td></tr>';

                    }

                    echo "</table>";

                } ?>

            </div>
        </section>

        <footer class="container text-center">
            <div class="col-sm-6 col-sm-offset-3">
                a service by <a href="http://nabble.nl">Nabble</a><br/>
                source available on <a href="https://github.com/nabble/semalt-blocker">GitHub</a>
            </div>
        </footer>
    
        <style>
            .status {
                display: none;
            }
        </style>

        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-27015911-4', 'auto');
            ga('send', 'pageview');
        </script>

    </body>
</html>