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
  public $items;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // read products
  function read($user, $results){
      // select all receipts
      $query = "SELECT vc_receipts.id AS id, vc_shops.name as shop, vc_receipts.date AS date
                FROM vc_receipts, vc_shops
                WHERE vc_receipts.shop = vc_shops.id AND user = ".$user->userid."
                ORDER BY date DESC
                LIMIT :results";

      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // bind params
      $stmt->bindParam(":results", $results, PDO::PARAM_INT);

      // execute query
      $stmt->execute();

      $receipt_arr = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        // read items for this receipt
        $query = "SELECT vc_transactions.id AS receipt_id, vc_categories.name AS category, vc_transactions.amount AS amount, vc_transactions.comment AS comment
                  FROM vc_transactions, vc_categories
                  WHERE vc_transactions.category = vc_categories.id AND vc_transactions.receipt_id = ".$id;

        // prepare query statement
        $item_stmt = $this->conn->prepare($query);

        // execute query
        $item_stmt->execute();

        $items_arr = array();
        while($row = $item_stmt->fetch(PDO::FETCH_ASSOC)){
          extract($row);

          $items_item=array(
            "id"=>$receipt_id,
            "category"=>$category,
            "amount"=>$amount,
            "comment"=>$comment
          );

          array_push($items_arr, $items_item);
        }

        $receipt_item=array(
          "id" => $id,
          "date" => $date,
          "shop" => $shop,
          "items" => $items_arr
        );

        array_push($receipt_arr, $receipt_item);
      }

      return $receipt_arr;
  }

  function create($user){
    // create the receipt
    $query = "INSERT INTO vc_receipts SET shop=:shop, user=:user, date=:date";

    // prepare query
    $stmt = $this->conn->prepare($query);

    // bind values
    $stmt->bindParam(":user", $user->userid);
    $stmt->bindParam(":date", $this->date);
    $stmt->bindParam(":shop", $this->shop);

    // execute query
    if(!$stmt->execute()){
      return false;
    }

    // get receipt id
    $insertid = $this->conn->lastInsertId();

    // insert all items
    foreach($this->items as &$item){
      // query
      $query = "INSERT INTO vc_transactions SET receipt_id=:receipt_id, category=:category, amount=:amount, comment=:comment";

      // prepare query
      $stmt = $this->conn->prepare($query);

      // bind values
      $stmt->bindParam(":receipt_id", $insertid);
      $stmt->bindParam(":category", $item->category);
      $stmt->bindParam(":amount", $item->amount);
      $stmt->bindParam(":comment", $item->comment);

      // execute query
      if(!$stmt->execute()){
        return false;
      }
    }

    return true;
  }
}
?>
