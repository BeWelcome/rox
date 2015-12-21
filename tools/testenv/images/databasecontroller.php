<?php
class DatabaseController
{
private $conn; // Instance of PDO

public function __construct($dsn,$user,$pw){
    $dsn = str_replace('mysqli', 'mysql', $dsn);
    $this->conn = new PDO ($dsn,$user,$pw);
}

public function selectQuery($ssql,$values = array()){
    $query = $this->conn->prepare($ssql);
    $query->execute($values);
    $query->setFetchMode(PDO::FETCH_ASSOC);
    $result = $query->fetchall();
    return $result;
}

}

?>