<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/user.php';

// error if parameters missing
if (Empty($_GET['username']) || Empty($_GET['password']) || Empty($_GET['identifier'])){
  print_r(json_encode(array("error" => "missing parameters")));
  exit;
}

// instantiate database object
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$user = new User($db);

// set user and password
$user->username = $_GET['username'];
$user->passwordPlain = $_GET['password'];

// set identifier
$user->identifier = $_GET['identifier'];

// call check method
if(!$user->checkCredentials()){
  print_r(json_encode(array("error" => $user->error)));
  exit;
}

// create array
$userArray = array(
    "error" => NULL,
    "username" => $user->username,
    "secret" =>  $user->serverSecret,
    "token" => $user->serverToken,
);

// make it json format
print_r(json_encode($userArray));
?>
