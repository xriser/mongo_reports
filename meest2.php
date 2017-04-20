<?php
/**
 * Created by PhpStorm.
 * User: riser
 * Date: 12.01.17
 * Time: 18:37
 */

header('Content-Type: text/html; charset=utf-8');

$login = "admin";
$password = "xtest";


//error_reporting(E_ALL);
//ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING); // E_ERROR | E_WARNING | E_PARSE | E_NOTICE
ini_set('display_errors', 1);

function auth_send(){
    header('WWW-Authenticate: Basic realm="Closed Zone"');
    header('HTTP/1.0 401 Unauthorized');
    echo "<html><body bgcolor=white link=blue vlink=blue alink=red>"
    ,"<h1>Ошибка аутентификации!</h1>"
    ,"</body></html>";
    exit;
};

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    auth_send();
} else {
    $auth_user = $_SERVER['PHP_AUTH_USER'];
    $auth_pass = $_SERVER['PHP_AUTH_PW'];

    if (($auth_user != $login) || ($auth_pass != $password)) {
        auth_send();
    };
};


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

$head.= '<p><b>Meest Express Stat</b></p>';
$head.= '<p>Check time: '.date("d-m-Y H:i:s").PHP_EOL.'</p>';

echo $head;

$msg = '<table><th>date</th><th>install_all</th><th>install_c8</th><th>install_other</th><th>launch</th><th>cab_new_user</th><th>basket</th><th>release</th>'.$msg;
//$msg .= '</table>';

echo $msg;

//mongodb://10.101.0.10:27017,10.101.0.27:27017?replicaSet=rs0&connectTimeoutMS=3000

//MongoCursor::$slaveOkay = false;

// cannot query secondary
//$cursor = $collection->find();

// can query secondary salveOkay();
//$cursor = $collection->find()->slaveOkay();

//MongoCursor::$slaveOkay = true;

// can query secondary
//$cursor = $collection->find();

// cannot query secondary
//$cursor = $collection->find()->slaveOkay(false);

//read_preference
//The read preference mode: MongoClient::RP_PRIMARY, MongoClient::RP_PRIMARY_PREFERRED, MongoClient::RP_SECONDARY, MongoClient::RP_SECONDARY_PREFERRED, or MongoClient::RP_NEAREST.

//echo date('01-m-Y');
//начало месяца
$start_month = strtotime(date('Y-m-01 00:00:00')); // == 1338534000
//echo $start_month;

$options = array("replicaSet" => "c8m", "readPreference" => MongoClient::RP_SECONDARY_PREFERRED);
$m = new MongoClient("mongodb://89.184.65.91:27017,89.184.66.74:27017?replicaSet=c8m&connectTimeoutMS=3000", $options);

//$m = new MongoClient("mongodb://m5.c8.net.ua:27017,m8.c8.net.ua:27017?replicaSet=c8t&connectTimeoutMS=3000");

//$cursor->setReadPreference(MongoClient::RP_NEAREST, array(

$collection = $m->ecom->tracking;

$data_cursor = $collection->find(['query.event_name' => ['$in' => [new MongoRegex('/install|launch|cab_new_user|basket|release/i')]],
                                  'ts' => ['$gt'=>$start_month]
                                    ]);
//->slaveOkay();

$data_cursor->sort(array('ts' => 1));

foreach ($data_cursor as $k=>$v){

        $date=date('d-m-Y', $v['ts']);

    if (preg_match('/install/i',$v['query']['event_name'])) {
        $data[$date]['install_all'] = $data[$date]['install_all'] + 1;

        if (preg_match('/referrer.*\=.*c8/i', $v['query']['event_name'])) {
            $data[$date]['install_c8'] = $data[$date]['install_c8'] + 1;
        } else {
            $data[$date]['install_other'] = $data[$date]['install_other'] + 1;
        }
    }
    //if (preg_match('/referrer/',$v['query']['event_name']) ) $data[$date]['install_r'] = $data[$date]['install_r'] +1;
    if ($v['query']['event_name'] == 'launch') $data[$date]['launch'] = $data[$date]['launch'] +1;
    if ($v['query']['event_name'] == 'cab_new_user')  $data[$date]['cab_new_user'] = $data[$date]['cab_new_user'] +1;
    if ($v['query']['event_name'] == 'basket')   $data[$date]['basket'] = $data[$date]['basket'] +1;
    if ($v['query']['event_name'] == 'release')  $data[$date]['release'] = $data[$date]['release'] +1;
}

$time_end = microtime(true);
$time = $time_end - $time_start;


foreach ($data as $k=>$v)
{
    if ($data[$k]['install_c8'] > 0 ) {
        echo "<tr><td><a href=meest_stat_c8_1.php?day=$k>$k</a></td>";
    } else {
        echo "<tr><td>$k</td>";
        
    }

    echo "<td>".number_format($data[$k]['install_all'])."</td>";
    echo "<td>".number_format($data[$k]['install_c8'])."</td>";
    echo "<td>".number_format($data[$k]['install_other'])."</td>";
    echo "<td>".number_format($data[$k]['launch'], 0, ',', ' ')."</td>";
    echo "<td>".number_format($data[$k]['cab_new_user'])."</td>";
    echo "<td>".number_format($data[$k]['basket'])."</td>";
    echo "<td>".number_format($data[$k]['release'])."</td>";
    echo "</tr>";


}
echo "</table>";


echo "<p class='sm_gr'> Generated in " . round($time,3)." sec. ";

$data_cursor->getNext();
echo "Reading from: ", $data_cursor->info()["server"], "</p>";
$m->close();

echo "</center>";



echo "</body></html>";
?>
