<?php
class User {
    //db and table
    private $conn;
    private $db_table = "user";

    //properties
    public $serverSecret;
    public $serverToken;
    public $serverSignature;
    public $username;
    public $userid;
    public $passwordPlain;
    public $passwordHashed;
    public $identifier;
    public $error;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    function validateCall($clientSignature, $clientToken, $clientTimestamp){
        // missing parameters
        if(Empty($clientSignature) || Empty($clientToken) || Empty($clientTimestamp)){
          $this->error = "missing header";
          return false;
        }

        // not older than 30s
        if(time() - $clientTimestamp > 30){
          $this->error ="timestamp too old";
          return false;
        }

        $query = "SELECT user, secret FROM token WHERE token = ?";
        $stmt = $this->conn->prepare($query);

        // bind
        $stmt->bindParam(1, $clientToken);

        // execute
        $stmt->execute();

        // no user with this token
        $num = $stmt->rowCount();
        if ($num == 0){
          $this->error = "combination not existing";
          return false;
        }

        // signature matches db secret+clientTimestamp
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->serverSecret = $row['secret'];
        $this->userid = $row['user'];

        $this->serverSignature = hash_hmac('sha256', $clientTimestamp, $this->serverSecret, true);
        $this->serverSignature = base64_encode($this->serverSignature);

        if(!hash_equals($clientSignature, $this->serverSignature)){
            $this->error = "wrong secret";
            return false;
        }

        // update last activity
        $query = "UPDATE token SET last_used = CURRENT_TIMESTAMP WHERE secret = ? and token = ?";
        $stmt = $this->conn->prepare($query);

        // bind
        $stmt->bindParam(1, $this->serverSecret);
        $stmt->bindParam(2, $clientToken);

        // execute
        $stmt->execute();

        // get user informations
        $query = "SELECT username FROM user WHERE id = :userid";
        $stmt = $this->conn->prepare($query);
        // bind
        $stmt->bindParam(":userid", $this->userid);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->username = $row['username'];

        return true;
    }

    function createUser(){
      //check username and mail
      $this->checkUsername();
      $this->checkMail();

      if(!isset($this->error)){

        //generate token and secret and check if they are unique
        $this->secret = base64_encode(random_bytes(24));
        $this->token = base64_encode(random_bytes(12));

        //hash password
        $this->passwordHashed = $this->passwordPlain;

        return true;
      }
      else {
        return false;
      }

    }

    function authError(){
      header('HTTP/1.1 401 Unauthorized', true, 401);
      exit;
    }

    function checkUsername(){
      $query = "SELECT username FROM ".$this->db_table." WHERE username = ?";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(1, $this->username);
      $stmt->execute();
      $num = $stmt->rowCount();
      if($num > 0){
        $this->error = "username taken";
      }
    }

    function checkMail(){
      $query = "SELECT mail FROM ".$this->db_table." WHERE mail = ?";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(1, $this->mail);
      $stmt->execute();
      $num = $stmt->rowCount();
      if($num > 0){
        $this->error = "mail taken";
      }
    }

    function checkCredentials(){
      $query = "SELECT id, username, password FROM ".$this->db_table." WHERE username = ?";
      $stmt = $this->conn->prepare($query);

      //bind
      $stmt->bindParam(1, $this->username);

      //execute
      $stmt->execute();

      $num = $stmt->rowCount();

      // no result = not existing account
      if ($num == 0) {
          $this->error = "account not existing";
          return false;
      }

      // fetch password hash
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->passwordHashed = $row['password'];

      // check if password is correct
      if (password_verify($this->passwordPlain, $this->passwordHashed)) {
        $this->userid = $row['id'];

        // create new pair of secret and token
        $this->serverSecret = base64_encode(random_bytes(24));
        $this->serverToken = base64_encode(random_bytes(12));

        $query = "INSERT INTO token SET user=:user, identifier=:identifier, token=:token, secret=:secret";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // bind values
        $stmt->bindParam(":user", $this->userid);
        $stmt->bindParam(":identifier", $this->identifier);
        $stmt->bindParam(":token", $this->serverToken);
        $stmt->bindParam(":secret", $this->serverSecret);


        // execute query
        if($stmt->execute()){
          return true;
          echo $this->serverToken.'\n';
          echo $this->serverSecret;
        } else {
	  //$this->error = "error creating new secret-token pair";
	  $this->error = $stmt->errorInfo();
	  return false;
        }

        return true;
      } else {
        $this->error = "wrong password";
        return false;
      }
    }

}
?>
