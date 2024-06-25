<?php

class DatabaseConnection {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli('db', 'username', 'password', 'mfcs');

        if ($this->connection->connect_error) {
            throw new Exception('Connection failed: ' . $this->connection->connect_error);
        }
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
}

?>