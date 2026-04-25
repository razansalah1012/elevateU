<?php
class Admin  {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

 
    public function isAdmin($userID) {
        $query = "SELECT role FROM users WHERE userID = $userID LIMIT 1";
        $result = mysqli_query($this->connection, $query);
    
        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            return $row['role'] === 'admin';
        }
        return false;
    }


    public function getAllUsers() {
        $query = "SELECT * FROM users";
        $result = mysqli_query($this->connection, $query);

        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        return $users;
    }



    public function banUser($userID) {
        $query = "UPDATE users SET isBanned = 1 WHERE userID = $userID";
        return mysqli_query($this->connection, $query);
    }


    public function unbanUser($userID) {
        $query = "UPDATE users SET isBanned = 0 WHERE userID = $userID";
        return mysqli_query($this->connection, $query);
    }

 
    public function deletePost($postID) {
        $query = "DELETE FROM posts WHERE post_id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $postID);
        return mysqli_stmt_execute($stmt);
    }
    public function getAllPosts() {
        $query = "SELECT posts.*, users.name 
                  FROM posts 
                  JOIN users ON posts.user_id = users.userID 
                  ORDER BY posts.timestamp DESC";
        $result = mysqli_query($this->connection, $query);
    
        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        return $posts;
    }
}
?>
