<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/user.php';
require_once '../objects/shops.php';


// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// instantiate auth object
$user = new User($db);

// set timestamp, token and signature
$clientSignature = $_SERVER['HTTP_X_AUTH_SIGNATURE'];
$clientToken = $_SERVER['HTTP_X_AUTH_TOKEN'];
$clientTimestamp = $_SERVER['HTTP_X_AUTH_TIMESTAMP'];

//validate call
if(!$user->validateCall($clientSignature, $clientToken, $clientTimestamp)){
    print_r(json_encode(array("error" => $auth->error)));
    exit;
}

// initialize object
$shops = new Shops($db);

// print transactions
print_r(json_encode($shops->read()));

?>
