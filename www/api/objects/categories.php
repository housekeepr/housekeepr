<?php
class Categories {
  //db and table
  private $conn;
  private $db_table = "vc_categories";

  // object properties
  public $id;
  public $name;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // read shops
  function read($user){
      // select all query
      $query = "SELECT id, name FROM ".$this->db_table." WHERE user = ".$user->userid." ORDER BY name";
      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // execute query
      $stmt->execute();

      // shops array
      $cat_arr=array();

      // retrieve our table contents
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
          extract($row);

          $cat_item=array(
              "id" => $id,
              "name" => $name,
          );

          array_push($cat_arr, $cat_item);
      }

      return $cat_arr;
  }
}
?>
