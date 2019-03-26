<?php
class PowerCounter {
  //db and table
  private $conn;

  // object properties
  public $id;
  public $name;
  public $user;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;
  }

  // get counters for user
  function getCounter(){
    // select all query
    $query = "SELECT id, number FROM power_counter WHERE user = ".$this->user."  ORDER BY standard DESC";
    // prepare query statement
    $stmt = $this->conn->prepare($query);

    // execute query
    $stmt->execute();

    // counter array
    $counter_arr=array();

    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $counter_item=array(
            "id" => $id,
            "number" => $number,
        );

        array_push($counter_arr, $counter_item);
    }

    return $counter_arr;
  }

  // get all readings for user
  function getReadings($user){
    // select all query
    $query = "SELECT power_readings.id, date, value, number FROM power_readings, power_counter WHERE power_readings.user = ".$user->userid."
              AND power_readings.counter = power_counter.id  ORDER BY date DESC";

    // prepare query statement
    $stmt = $this->conn->prepare($query);

    // execute query
    $stmt->execute();

    // counter array
    $readings_arr = array();

    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);

        $reading_item=array(
            "id" => $id,
            "date" => $date,
            "value" => $value,
            "number" => $number,
        );

        array_push($readings_arr, $reading_item);
    }

    return $readings_arr;
  }

  // create product
  function addReading($user){

    // query to insert record
    $query = "INSERT INTO power_readings SET user=:user, date=:date, value=:value, counter=:counter";

    // prepare query
    $stmt = $this->conn->prepare($query);

    // sanitize
    $this->date=htmlspecialchars(strip_tags($this->date));
    $this->value=htmlspecialchars(strip_tags($this->value));
    $this->counter=htmlspecialchars(strip_tags($this->counter));

    // bind values
    $stmt->bindParam(":user", $user->userid);
    $stmt->bindParam(":date", $this->date);
    $stmt->bindParam(":value", $this->value);
    $stmt->bindParam(":counter", $this->counter);

    // execute query
    if($stmt->execute()){
        return true;
    }

    return false;

  }
}
?>
