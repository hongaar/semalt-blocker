<?php
require '../vendor/autoload.php';
\Nabble\SemaltBlocker\Blocker::protect();

const STATUS_GREEN = 2;
const STATUS_ORANGE = 3;
const STATUS_RED = 4;

?>
<html>
<head>
    <title>semalt-blocker debug console</title>
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

        input[name=url] {
            width: 30em;
        }

        .progress {
            width: 50%;
            display: inline-block;
        }

        .table {
            width: auto;
            margin: 0 auto;
        }

        .table span.success {
            color: green;
        }

        .table span.warning {
            color: orange;
        }

        .table span.danger {
            color: red;
        }

        span {
            font-weight: bold;
        }

        footer > div {
            padding-top: 50px;
            margin-bottom: 50px;
        }
    </style>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script>
        $(function () {
            var addhttp = function (e) {
                var $input = $('input[name=url]');
                if ($input.val() && !$input.val().match(/^https?:/)) {
                    $input.val('http://' + $input.val());
                }
            };
            $('input[name=url]').on('blur', addhttp);
            $('form').on('submit', addhttp);
        });
        var progress = function () {
            var total = $('table').data('total');
            var list = ['warning', 'danger', 'success'];
            for (var i in list) {
                var cat = list[i];
                var count = $('table span.' + cat).length;
                var perc = parseInt(count / total * 100);
                $('.progress-bar-' + cat).css('width', perc + '%').text(perc + '%');
            }
        };
    </script>
</head>

<body>
<section class="container">
    <div class="col-md-8 col-md-offset-2 text-center">

        <h1><a href="https://github.com/nabble/semalt-blocker">semalt-blocker</a> debug console</h1>

        <form method="get">
            <?php
            $url = isset($_GET['url']) ?
                $_GET['url'] :
                '';
            //                        "http://" . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['REQUEST_URI']) . 'target.php';

            $url = filter_var($url, FILTER_VALIDATE_URL);

            ?>
            <input type="text" name="url" placeholder="your website url" value="<?php echo htmlspecialchars($url); ?>"/>
            <button type="submit">debug url</button>
        </form>

    </div>
    <div class="col-md-12 text-center">

        <?php if (isset($_GET['url']) && empty($_GET['url'])) {

            echo 'No URL provided';

        } else if (isset($_GET['url']) && $_GET['url']) {

        function status($code)
        {
            if (substr($code, 0, 1) == '2') return STATUS_RED;
            if (substr($code, 0, 1) == '3') return STATUS_ORANGE;
            return STATUS_GREEN;
        }

        function statusHtml($status, $redirect = null)
        {
            if ($status == STATUS_RED) return '<span class="danger">Not blocked</span>';
            if ($status == STATUS_ORANGE) return '<span class="warning">Redirect </span> &rarr; <a href="?url=' . urlencode($redirect) . '">' . $redirect . '</a>';
            return '<span class="success">Blocked</span>';
        }

        ob_implicit_flush(true);
        ob_end_flush();

        $list = [];

        if ($url) {

            $list = \Nabble\SemaltBlocker\Blocker::getBlocklist();
            $client = new \Guzzle\Http\Client(null, array('redirect.disable' => true));

        }

        ?>

        <p><a href=".">recent scores</a></p>
        <div class="progress">
            <div class="progress-bar progress-bar-success" style="width: 0%"></div>
            <div class="progress-bar progress-bar-warning" style="width: 0%"></div>
            <div class="progress-bar progress-bar-danger" style="width: 0%"></div>
        </div>
        <table class='table table-bordered table-condensed table-hover' data-total='<?php echo count($list); ?>'>

            <?php
            $greens = 0;
            foreach ($list as $k => $referral) {

                $request = $client->get($url, [
                    'Referer' => 'http://' . $referral
                ]);

                $redirect = false;
                try {
                    $response = $request->send();
                    if ($response->getStatusCode() == 302 || $response->getStatusCode() == 301) {
                        $redirect = (string)$response->getHeader('Location');
                    }
                } catch (Guzzle\Http\Exception\BadResponseException $e) {
                    $response = $e->getResponse();
                } catch (Exception $e) {
                    $response = false;
                }

                $status = STATUS_RED;
                if ($response) {
                    $status = status($response->getStatusCode());
                    echo "<tr><th>" . $referral . '</th><td>' . statusHtml($status, $redirect) . '</td></tr>';
                }

                if ($status == STATUS_GREEN)
                    $greens++;

                echo "<script>progress();</script>";
            }

            if (count($list)) {
                $perc = str_pad((int)($greens / count($list) * 100), 3, "0", STR_PAD_LEFT);
                file_put_contents('stats/' . base64_encode($url), $url . PHP_EOL . $perc . PHP_EOL . time() . PHP_EOL);
            }

            echo "</table>";

            } else {

                $files = glob('stats/*');
                usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
                ?>

                <h3>Recent scores</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>URL</th>
                            <th>Blocked</th>
                        </tr>
                    </thead>

                <?php
                $i = 0;
                foreach ($files as $file) {
                    $i++;
                    $results = explode(PHP_EOL, file_get_contents($file));
                    ?>
                    <tr>
                        <td><?php echo date('c', $results[2]); ?></td>
                        <td><?php echo htmlentities($results[0]); ?></td>
                        <td><?php echo (int) $results[1]; ?>%</td>
                    </tr>
                    <?php
                    if ($i > 10) break;
                }
                ?>

                </table>

                <?php
            } ?>

    </div>
</section>

<footer class="container text-center">
    <hr/>
    <div class="col-sm-6 col-sm-offset-3">
        a service by <a href="http://nabble.nl">Nabble</a><br/>
        source available on <a href="https://github.com/nabble/semalt-blocker">GitHub</a>
    </div>
</footer>

<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-27015911-4', 'auto');
    ga('send', 'pageview');
</script>

</body>
</html>