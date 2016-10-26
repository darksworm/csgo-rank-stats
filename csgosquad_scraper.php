<?php

require_once 'helpers.php';
require_once 'SimpleCURL.php';

const URL = "https://csgosquad.com/ranks";

$site = SimpleCURL::getSite(URL);

if ($site == NULL) {
    fail("getSite returned NULL");
} else {

    if($site->responseCode >= 400){
        fail("Server Error (" . $site->responseCode . ")");
        $up = 0;
    } else {
        $data = getJSON($site->xpath);

        if (!$data) {
            fail("JSON not found");
        } else {
            update($data);
        }
        $up = 1;
    }

    $db = getDBConnection();
    $query = "INSERT INTO status(up, time) VALUES(:up, :time);";
    $dbh = $db->prepare($query);
    $dbh->execute([':up' => $up, 'time' => time()]);
}

function update($data)
{
    $db = getDBConnection();
    $data->time = substr($data->time, 0, -3);
    // check if we already have data for this timestamp
    $query = "SELECT time FROM ranks WHERE time=:time";
    $dbh = $db->prepare($query);
    $dbh->execute([":time" => $data->time]);
    $result = $dbh->fetch();

    if (!$result || $result['time'] != $data->time) {
        $day = 0;
        $distribution = [];
        do {
            if (!empty($data->days[$day]->distribution)) {
                $distribution = $data->days[$day]->distribution;
                break;
            }
        } while (isset($data->days[++$day]));

        if (empty($distribution)) {
            fail("CSGOSQUAD had no data on rank distribution");
            return;
        }

        $query = "INSERT INTO ranks(time,total,ranked,1a,2a,3a,4a,5a,6a,7a,8a,9a,10a,
        11a,12a,13a,14a,15a,16a,17a,18a) VALUES (:time, :total, :ranked, ";

        for ($i = 0; $i < 18; $i++) {
            $adj = $i + 1;
            $query .= ":" . $adj . ", ";
            if(!isset($distribution[$i])){
                fail("CSGOSQUAD had missing data for rank: " . RANKS[$i - 1]);
                return;
            } else {
                $vals[":" . $adj] = $distribution[$i];
            }
        }
        $query = substr($query, 0, -2);
        $query .= ");";

        $vals[":time"] = $data->time;
        $vals[":total"] = $data->days[$day]->total;
        $vals[":ranked"] = $data->days[$day]->totalWithoutUnranked;

        $sth = $db->prepare($query);
        $sth->execute($vals);
    }

    echo $data->time . PHP_EOL;
}

function fail($msg)
{
    echo "ERROR: " . $msg . " " . (new \DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
}

function getJSON($dom)
{
    $results = $dom->query("//body/script[contains(text(),'totalWithoutUnranked')]");
    if(!$results->length){
        return null;
    }
    $script = $results->item(0)->nodeValue;
    $json = substr(substr($script, strlen('var data = ')), 0, -1);
    $data = json_decode($json);
    return $data;
}

/*
 *  DB SCHEMA
create database csgosquad;
create user 'csgosquad' identified by '';
use csgosquad;

create table ranks(1a DECIMAL(18,15) NOT NULL,
2a DECIMAL(18,15) NOT NULL,
3a DECIMAL(18,15) NOT NULL,
4a DECIMAL(18,15) NOT NULL,
5a DECIMAL(18,15) NOT NULL,
6a DECIMAL(18,15) NOT NULL,
7a DECIMAL(18,15) NOT NULL,
8a DECIMAL(18,15) NOT NULL,
9a DECIMAL(18,15) NOT NULL,
10a DECIMAL(18,15) NOT NULL,
11a DECIMAL(18,15) NOT NULL,
12a DECIMAL(18,15) NOT NULL,
13a DECIMAL(18,15) NOT NULL,
14a DECIMAL(18,15) NOT NULL,
15a DECIMAL(18,15) NOT NULL,
16a DECIMAL(18,15) NOT NULL,
17a DECIMAL(18,15) NOT NULL,
18a DECIMAL(18,15) NOT NULL,
time int(11) not null,
total int not null,
ranked int not null);

create table status(up tinyint(1) NOT NULL, time int(11) NOT NULL);

grant ALL on csgosquad.* to 'csgosquad';
 */