<?php
header('Content-Type: text/html; charset=utf-8');

$login = "admin";
$password = "admin";


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

echo "Work";