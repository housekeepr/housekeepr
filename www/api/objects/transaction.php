<?php
class Transaction{
  // database connection and table name
  private $conn;
  private $table_name = "fixkosten_transactions";

  // object properties
  public $id;
  public $date;
  public $category;
  public $type;
  public $comment;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // read products
  function read(){
      // select all query
      $query = "SELECT id, date, category, type, comment, monthly_date, amount FROM " . $this->table_name;
      // prepare query statement
      $stmt = $this->conn->prepare($query);

      // execute query
      $stmt->execute();

      return $stmt;
  }

  function readSingle(){

  }
}
