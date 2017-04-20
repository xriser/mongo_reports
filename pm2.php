<?php
/**
 * Created by PhpStorm.
 * User: riser
 * Date: 24.02.17
 * Time: 14:52
 */

$time_start=microtime(true);

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

$head.= '<p><b>PariMatch shahter_vs_selta Stat</b></p>';
$head.= '<p>Check time: '.date("d-m-Y H:i:s").PHP_EOL.'</p>';

echo $head;

$msg = '<table><th>#</th><th>device</th><th>time</th>'.$msg;
//$msg .= '</table>';

echo $msg;

$options = array("replicaSet" => "c8m", "readPreference" => MongoClient::RP_SECONDARY_PREFERRED);
$m = new MongoClient("mongodb://89.184.65.91:27017,89.184.66.74:27017?replicaSet=c8m&connectTimeoutMS=3000", $options);

//$m = new MongoClient("mongodb://m5.c8.net.ua:27017,m8.c8.net.ua:27017?replicaSet=c8t&connectTimeoutMS=3000");
//$cursor->setReadPreference(MongoClient::RP_NEAREST, array(

$collection = $m->ecom->tracking;

//$data_cursor = $collection->find(['query.device_model' => ['$regex' => [new MongoRegex('/^Nexus/i')]]]);

$data_cursor = $collection->find([
        '$and' =>[
            ['query.app_id' =>       ['$in' => [new MongoRegex('/PariMatch/i')]]],
            ['query.device_model' => ['$in' => [new MongoRegex('/./i')]]],
            ['query.device_model' => ['$in' => [new MongoRegex('/^(?!.*device_model|.*Android|.*Unknown).*/i')]]],
        ]]
);
//['query.device_model' => ['$in' => [new MongoRegex('/[^\s,{device_model},Android].*/i')]]],
//$^(?!.*STRING1|.*STRING2|.*STRING3).*
//->slaveOkay();

$data_cursor->sort(array('ts' => 1));

$i=0;
foreach ($data_cursor as $k=>$v){
    $i++;
    $date=date('d-m-Y H:i:s', $v['ts']);

    echo '<tr><td>'.$i.'</td>';
    echo '<td>'.$v['query']['device_model'].'</td>';
    echo '<td>'.$date.'</td></tr>';


}

$time_end = microtime(true);
$time = $time_end - $time_start;


echo "</table>";


echo "<p class='sm_gr'> Generated in " . round($time,3)." sec. ";

$data_cursor->getNext();
echo "Reading from: ", $data_cursor->info()["server"], "</p>";
$m->close();

echo "</center>";



echo "</body></html>";
?>