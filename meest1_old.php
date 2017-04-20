<?php
/**
 * Created by PhpStorm.
 * User: riser
 * Date: 12.01.17
 * Time: 16:12
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
$head .= '    table {margin-top:20px;}';
$head .= '    td {border:1px solid #cdcdcd; padding:2px 20px;text-align:center}';
$head .= '</style>';

$head .= '<html>';
$head .= '<body>';
$head .= '<center>';
$head .= '<p>&nbsp;</p>';

$head.= '<p><b>Meest Express Stat</b></p>';
$head.= '<p>Check time: '.date("d-m-Y H:i:s").PHP_EOL.'</p>';

echo $head;

$msg = '<table><th>date</th><th>install</th><th>launch</th><th>cab_new_user</th><th>basket</th><th>release</th>'.$msg;
//$msg .= '</table>';

echo $msg;


$m = new MongoClient("mongodb://m3.c8.net.ua:27018");
$collection = $m->logs->tracking;

$data = $collection->find(['query.event_name' => ['$in'=>['launch', 'install', 'cab_new_user', 'basket', 'release']]]);


$data->sort(array('ts' => 1));

//print_r($data);
$count=0;
$launch=0;
$install=0;
$cab_new_user=0;
$basket=0;
$release=0;

foreach ($data as $k=>$v){

    if (date('d-m-Y', $v['ts']) == $date || !$date) {
        if ($v['query']['event_name'] == 'launch') $launch++;
        if ($v['query']['event_name'] == 'install') $install++;
        if ($v['query']['event_name'] == 'cab_new_user') $cab_new_user++;
        if ($v['query']['event_name'] == 'basket') $basket++;
        if ($v['query']['event_name'] == 'release') $release++;


    } else {
        echo "<tr><td>".$date."</td><td>".$install."</td><td>".$launch."</td>";
        echo "<td>".$cab_new_user."</td>";
        echo "<td>".$basket."</td>";
        echo "<td>".$release."</td>";
        echo "</tr>";
        $launch=0;
        $install=0;
        $cab_new_user=0;
        $basket=0;
        $release=0;

    }
    $date=date('d-m-Y', $v['ts']);

}
// print last day
echo "<tr><td>".$date."</td><td>".$install."</td><td>".$launch."</td>";
echo "<td>".$cab_new_user."</td>";
echo "<td>".$basket."</td>";
echo "<td>".$release."</td>";
echo "</tr>";

echo "</table>";

$time_end = microtime(true);
$time = $time_end - $time_start;

echo "generated in " . round($time,3);
echo "</center>";

echo "</body></html>";
?>