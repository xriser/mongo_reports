<?php
/**
 * Created by PhpStorm.
 * User: riser
 * Date: 10.02.17
 * Time: 12:04
 */


$day = $_GET['day'];

$fromday_unx = strtotime($day);
$today_unx = strtotime("$day + 1day");

$time_start = microtime(true);
//echo strtotime($day).' = '.date('d-m-Y',strtotime($day)).'<BR>';
//echo strtotime("$day + 1day").' = '.date('d-m-Y', strtotime("$day + 1day")).'<BR>';


$start_month = strtotime(date('Y-m-01 00:00:00')); // == 1338534000
//echo $start_month;

$options = array("replicaSet" => "c8m", "readPreference" => MongoClient::RP_SECONDARY_PREFERRED);
$m = new MongoClient("mongodb://89.184.65.91:27017,89.184.66.74:27017?replicaSet=c8m&connectTimeoutMS=3000", $options);

//$m = new MongoClient("mongodb://m5.c8.net.ua:27017,m8.c8.net.ua:27017?replicaSet=c8t&connectTimeoutMS=3000");

//$cursor->setReadPreference(MongoClient::RP_NEAREST, array(

$collection = $m->ecom->tracking;

$data_cursor = $collection->find([
        '$and' => [
            ['query.event_name' => ['$in' => [new MongoRegex('/C8_/i')]]],
            ['ts' => ['$gt' => $fromday_unx]],
            ['ts' => ['$lt' => $today_unx]]
        ]]
);
//->slaveOkay();

//$data_cursor->sort(array('ts' => 1));

foreach ($data_cursor as $k => $v) {

    preg_match('/(?<=c8_)(.*)/siu', $v['query']['event_name'], $matches);
    //echo $v['query']['event_name'].'<br>';
    $source[] = $matches[0];
//    var_dump($matches[0]);

}

$values_count = (array_count_values($source));


$head = '<style>';
$head .= ' table {margin-top:20px;}
      td {border:1px solid #cdcdcd; padding:2px 20px; text-align:center}
      th {border:1px solid #cdcdcd; padding:4px 10px; text-align:center; background: #f1f1f1;}
      a {color: #428bca; text-decoration: none;}
      body {color: #333;}
      .sm_gr {color: #666; font-size:12px}
      ';
$head .= '</style>';

$head .= '<html>';
$head .= '<body>';
$head .= '<center>';
$head .= '<p>&nbsp;</p>';

$head .= '<p><b>Meest Express Stat</b></p>';
$head .= '<p>Check time: ' . $day . PHP_EOL . '</p>';

echo $head;

$msg = '<table><th>source</th><th>count</th>' . $msg;
//$msg .= '</table>';

echo $msg;


foreach ($values_count as $k => $v) {
    echo '<tr><td>' . $k . '</td><td>' . $v . '</td><tr>';

}

echo "</table>";


$time_end = microtime(true);
$time = $time_end - $time_start;


echo "<p class='sm_gr'> Generated in " . round($time, 3) . " sec. ";
$data_cursor->getNext();
echo "Reading from: ", $data_cursor->info()["server"], "</p>";
$m->close();

echo "</center>";


echo "</body></html>";


?>