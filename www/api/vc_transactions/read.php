<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/user.php';
require_once '../objects/vc_transactions.php';


// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$user = new User($db);

// validate call
if(!$user->validateCall($_SERVER['HTTP_X_AUTH_SIGNATURE'], $_SERVER['HTTP_X_AUTH_TOKEN'], $_SERVER['HTTP_X_AUTH_TIMESTAMP'])){
    print_r(json_encode(array("error" => $user->error)));
    exit;
}

// initialize object
$transaction = new VCTransactions($db);

// print transactions
print_r(json_encode($transaction->read($user)));

?>
