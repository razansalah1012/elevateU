<?php
class User {
    private $connection;

    public function __construct($dbConnection) {
        if (!$dbConnection) {
            die("❌ Database connection failed.");
        }
        $this->connection = $dbConnection;
    }
    
    
    public function register($name, $email, $password, $role = 'user') {
        $query = "INSERT INTO users (name, email, password, role) 
                  VALUES ('$name', '$email', '$password', '$role')";

        return mysqli_query($this->connection, $query);
    }

    
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($this->connection, $query);
    
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if ($user['isBanned']) {
                echo "❌ Your account has been banned.";
                return false;
            }
            if ($password == $user['password']) {
                return $user;
            }
        }
    
        return false;
    }
    


    public function getUserById($userID) {
        $query = "SELECT * FROM users WHERE userID = $userID";
        $result = mysqli_query($this->connection, $query);
        return mysqli_fetch_assoc($result);
    }

    public function updateUser($userID, $name, $email) {
        $query = "UPDATE users SET name = '$name', email = '$email' WHERE userID = $userID";
        return mysqli_query($this->connection, $query);
    }
    public function deleteUser($userID) {
        $query = "DELETE FROM users WHERE userID = $userID";
        return mysqli_query($this->connection, $query);
    }
    public function getBio($userID) {
        $query = "SELECT bio FROM profiles WHERE user_id = $userID";
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
        return $row ? $row['bio'] : null;
    }
    public function isPremium($userID) {
        $query = "SELECT isPremium FROM users WHERE userID = $userID";
        $result = mysqli_query($this->connection, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            return (bool)$row['isPremium'];
        }
        return false;
    }
}
?>