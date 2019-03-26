<?php
class Shops {
  //db and table
  private $conn;
  private $db_table = "vc_shops";

  // object properties
  public $id;
  public $name;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // read shops
  function read(){
      // select all query
      $query = "SELECT id, name FROM " . $this->db_table . " ORDER BY name";
      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // execute query
      $stmt->execute();

      // shops array
      $shops_arr=array();

      // retrieve our table contents
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          extract($row);

          $shop_item=array(
              "id" => $id,
              "name" => $name,
          );

          array_push($shops_arr, $shop_item);
      }

      return $shops_arr;
  }
}
?>
