<?php
    class Database {
	    private $_connection;
	    private static $_instance; //The single instance

        /*
        Get an instance of the Database
        @return Instance
        */
        public static function getInstance() {
            if(!self::$_instance) { // If no instance then make one
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        // Constructor
        private function __construct() {
            $config = include(RESOURCE_PATH .'/config.php');
            try {
                $this->_connection = new PDO("mysql:host=" . $config["db"]["host"] . ";dbname=". $config["db"]["dbname"], 
                                        $config["db"]["username"], $config["db"]["password"]);
                // set the PDO error mode to exception
                $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
                trigger_error("Failed to conencto to MySQL: " . $e->getMessage(),
                    E_USER_ERROR);
            }
        }
        // Magic method clone is empty to prevent duplication of connection
        private function __clone() { }
        // Get  connection
        public function getConnection() {
            return $this->_connection;
        }

        public function queryDB($query){
            return $this->_connection->query($query)->fetchAll();
        }
        public function selectByID($table, $idCol, $id){
            $query = "SELECT * FROM ".$table." WHERE ".$idCol." LIKE '".$id."';";
            return $this->queryDB($query);
        }
    }
?>