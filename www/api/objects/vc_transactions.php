<?php
class VCTransactions {
  // database connection and table name
  private $conn;
  private $table_name = "vc_transactions";

  // object properties
  public $id;
  public $date;
  public $category;
  public $type;
  public $comment;
  public $amount;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // read products
  function read($user){
      // select all query
      $query = "SELECT vc_transactions.id AS id, vc_transactions.date AS date, vc_transactions.amount AS amount,
                vc_shops.name AS shop, vc_categories.name AS category, vc_transactions.comment AS comment,
                vc_shops.logo AS logo
                FROM vc_transactions, vc_shops, vc_categories
                WHERE vc_transactions.shop = vc_shops.id AND vc_transactions.category = vc_categories.id
                AND vc_transactions.user = ".$user->userid." ORDER BY date DESC, shop DESC";

      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // execute query
      $stmt->execute();

      $transaction_arr = array();

      // retrieve our table contents
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          extract($row);

          $transaction_item=array(
              "id" => $id,
              "date" => $date,
              "amount" => $amount,
              "shop" => $shop,
              "logo" => $logo,
              "category" => $category,
              "comment" => $comment,
          );

          array_push($transaction_arr, $transaction_item);
        }

      return $transaction_arr;
  }

  function create($user){
    // query
    $query = "INSERT INTO vc_transactions SET user=:user, date=:date, shop=:shop, category=:category, amount=:amount, comment=:comment";

    // prepare query
    $stmt = $this->conn->prepare($query);

    // bind values
    $stmt->bindParam(":user", $user->userid);
    $stmt->bindParam(":date", $this->date);
    $stmt->bindParam(":shop", $this->shop);
    $stmt->bindParam(":category", $this->category);
    $stmt->bindParam(":amount", $this->amount);
    $stmt->bindParam(":comment", $this->comment);

    // execute query
    if($stmt->execute()){
      return true;
    } else {
      return false;
    }
  }
}
?>
