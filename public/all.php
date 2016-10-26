<?php
require_once '../helpers.php';

$db = getDBConnection();

$sth = $db->query("select * from ranks order by time desc limit 1");
$sth->execute();
$piedata = $sth->fetch();

$sth = $db->query("SELECT * FROM (SELECT 
                      avg(1a) as 1a,avg(2a) as 2a,avg(3a) as 3a,avg(4a) as 4a,avg(5a) as 5a,avg(6a) as 6a,
                      avg(7a) as 7a,avg(8a) as 8a,avg(9a) as 9a,avg(10a) as 10a,avg(11a) as 11a,avg(12a) as 12a,
                      avg(13a) as 13a,avg(14a) as 14a,avg(15a) as 15a,avg(16a) as 16a,avg(17a) as 17a,avg(18a) as 18a, 
                      date(from_unixtime(time)) as dateyes 
                    FROM ranks 
                    group by dateyes 
                    ORDER BY dateyes DESC 
                    LIMIT 40) AS t ORDER BY t.dateyes");
$sth->execute();
$points = $sth->fetchAll();

$sth = $db->query("SELECT up, from_unixtime(time) as dateyes FROM status ORDER BY time DESC LIMIT 1");
$sth->execute();
$status = $sth->fetch();

$parsedData = [];
foreach ($points as $i => $point) {
    foreach (RANKS as $j => $rank) {
        if (!isset($parsedData[$j])) {
            $parsedData[$j] = [];
        }
        array_push($parsedData[$j], $point[(($j + 1) . 'a')]);
    }
}
?>
<!--suppress HtmlUnknownTarget -->
<head>
    <title>CS:GO rank stats</title>
</head>
<!doctype html>
<html>
<head>
    <script src="chart.js"></script>
    <link rel="stylesheet" type="text/css" href="index.css">
</head>
<header>
    <b>Disclaimer: All of this information belongs to <a style="color:#9C6F1F" href="https://csgosquad.com"
                                                         target="_blank">csgosquad</a> <span id="website-status">and their website is
        <?php if ($status['up']): ?><b style="color:#00B233">available</b><?php else: ?><b
            style="color:#E8550C">not available</b><?php endif; ?></span><span id="last-check">, last check: <?= $status['dateyes'] ?> (UTC)</span></b>
</header>
<body>
<div style="padding-top: 20px"></div>
<div id="chart-cont">
    <?php foreach (RANKS as $i => $rank): ?>
        <div class="row">
            <div class="subrow">
                <h2 class="title"><a style="color:#595550" href="/csgo?rank=<?= SHORT_RANKS[$i] ?>"><?= $rank ?></a></h2>
                <canvas class="chart" id="canvas<?= $i ?>"></canvas>
            </div>
        </div>
    <?php endforeach; ?>
    <div id="pie-cont">
        <h1>ALL RANKS</h1>

        <div class="pie">
            <div id="canvas-holder">
                <canvas id="chart-area" width="500" height="500"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    <?php foreach(RANKS as $i => $rank): ?>
    // individual rank graphs
    var lineChartData<?=$i?> = {
        labels: [<?php foreach ($points as $j => $c) {
            echo '"' . $c['dateyes'];
            if ($j != count($points) - 1) {
                echo '",';
            } else echo '"';
        } ?>],
        datasets: [
            {
                label: "<?=$rank?>",
                fillColor: "rgba(61,89,171,0.2)",
                strokeColor: "rgba(61,89,171,1)",
                pointColor: "rgba(61,89,171,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(61,89,171,1)",
                data: [
                    <?php foreach ($parsedData[$i] as $d => $data) {
                    echo '"' . round($data, 3) . '"';
                    if ($d != count($parsedData[$i]) - 1) echo ',';
                }?>
                ]
            } <?php if ($i < 17) echo ','; ?>
        ]
    };
    <?php endforeach; ?>

    // all rank pie chart
    var pieData = [
        <?php for($i = 1; $i < 19; $i++): ?>
        {
            value: <?= $piedata[($i . 'a')]?>,
            color: "<?= COLORS[$i]?>",
            label: "<?= RANKS[($i - 1)]?>"
        },
        <?php endfor; ?>
    ];
    window.onload = function () {
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myPie = new Chart(ctx).Pie(pieData);
        <?php foreach(RANKS as $i => $rank): ?>
        var z = document.getElementById("canvas<?=$i?>").getContext("2d");
        window.myLine = new Chart(z).Line(lineChartData<?=$i?>, {
            responsive: true,
            pointHitDetectionRadius: 1
        });
        <?php endforeach; ?>
    };
</script>
</body>
</html>
