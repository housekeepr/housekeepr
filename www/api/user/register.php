<?php
//required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//include database and object files
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/user.php';

//instantiate database object
$database = new Database();
$db = $database->getConnection();

//instantiate auth object
$auth = new User($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

$auth->username = $data->username;
$auth->passwordPlain = $data->password;
$auth->mail = $data->mail;

$result = $auth->createUser();
if($result){
  $output = array(
      "status" => "created",
      "secret" => $auth->secret,
      "token" => $auth->token,
  );
  echo json_encode($output);
}
else{
  $output = array(
      "error" => $auth->error,
  );
  echo json_encode($output);
}
//print_r($_POST);
//echo file_get_contents("php://input");
//print_r($data);
//$auth->username = $_POST['username'];
//$auth->password = $_POST['password'];
//$auth->email = $_POST['email'];
?>
