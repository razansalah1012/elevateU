<?php
class DBController {
    private $host = "sql312.infinityfree.com";
    private $user = "if0_39315924";
    private $password = "redwab10003311";
    private $database = "if0_39315924_elevateu";
    public $connection;

    public function openConnection() {
        $this->connection = new mysqli(
            $this->host, $this->user, $this->password, $this->database
        );

        if ($this->connection->connect_error) {
            echo "Error in Connection: " . $this->connection->connect_error;
            return false;
        }
        return true;
    }

    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        } else {
            echo "Connection is not opened.";
        }
    }

    public function select($query) {
        $result = $this->connection->query($query);
        if (!$result) {
            echo "Error: " . mysqli_error($this->connection);
            return false;
        }
        return $result;
    }
}
?>
