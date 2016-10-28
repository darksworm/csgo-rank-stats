<?php
require_once '../helpers.php';

function backToAll()
{
    include('all.php');
    exit;
}

if (in_array($_GET['rank'], SHORT_RANKS) === false) {
    backToAll();
}

$id = array_search($_GET['rank'], SHORT_RANKS) + 1;

if (!$id) {
    backToAll();
}

$db = getDBConnection();
$sth = $db->query("SELECT * FROM (SELECT 
                      avg(" . $id . "a) as val, 
                      date(from_unixtime(time)) as dateyes 
                    FROM ranks 
                    group by dateyes 
                    ORDER BY dateyes DESC 
                    LIMIT 100) AS t ORDER BY t.dateyes");
$sth->execute();
$points = $sth->fetchAll();

?>
<head>
    <script src="chart.js"></script>
    <link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
    <h2><?= RANKS[$id - 1] ?></h2>
    <div id="chart-wrapper-wrapper">
        <div id="chart-wrapper">
            <canvas class="chart" id="canvas"></canvas>
        </div>
    </div>
</body>
<script>
    var lineChartData = {
        labels: [<?php foreach ($points as $j => $c) {
            echo '"' . $c['dateyes'];
            if ($j != count($points) - 1) {
                echo '",';
            } else echo '"';
        } ?>],
        datasets: [
            {
                label: "<?= RANKS[$id - 1] ?>",
                fillColor: "rgba(61,89,171,0.2)",
                strokeColor: "rgba(61,89,171,1)",
                pointColor: "rgba(61,89,171,1)",
                pointStrokeColor: "#fff",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(61,89,171,1)",
                data: [
                    <?php foreach ($points as $d => $data) {
                    echo '"' . round($data['val'], 3) . '"';
                    if ($d != count($points) - 1) echo ',';
                }?>
                ]
            }
        ]
    };

    window.onload = function () {
        var wrapper = document.getElementById('chart-wrapper');

        var width = Math.floor((Math.max(document.documentElement.clientHeight, window.innerHeight || 0) / 9) * 16);
        width = Math.floor(width + (width / 100 * 4));
        wrapper.style.width = width + 'px';

        var z = document.getElementById("canvas").getContext("2d");
        window.myLine = new Chart(z).Line(lineChartData, {
            responsive: true,
            pointHitDetectionRadius: 1
        });
    };
</script>