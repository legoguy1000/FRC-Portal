<?php

class Database {
    private static $instance = NULL;

    public static function getInstance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query() {
      // Connect to the database
      $db = ;

      // Query the database
      $result = $db->query($query);
      if($result === false) {
        db_error($query);
        return false;
      }
      return $result;
    }
}

$class = DemoSingleton::getInstance();




?>
