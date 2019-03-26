<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/transaction.php';
require_once '../objects/user.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// instantiate auth object
$auth = new User($db);

// set timestamp, token and signature
$clientSignature = $_SERVER['HTTP_X_AUTH_SIGNATURE'];
$clientToken = $_SERVER['HTTP_X_AUTH_TOKEN'];
$clientTimestamp = $_SERVER['HTTP_X_AUTH_TIMESTAMP'];

//validate call
if(!$auth->validateCall($clientSignature, $clientToken, $clientTimestamp)){
    print_r(json_encode(array("error" => $auth->error)));
    exit;
}

// initialize object
$transaction = new Transaction($db);

// query products
$stmt = $transaction->read();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){

    // products array
    $transactions_arr=array();

    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $transaction_item=array(
            "id" => $id,
            "date" => $date,
            "comment" => $comment,
            "monthly_date" => $monthly_date,
            "amount" => $amount,
        );

        array_push($transactions_arr, $transaction_item);
    }

    echo json_encode($transactions_arr);
}

else{
    echo json_encode(
        array("message" => "No transactions found.")
    );
}
?>
