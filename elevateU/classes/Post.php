<?php
class Post {
    private $connection;

    public function __construct($dbConnection) {
        $this->connection = $dbConnection;
    }

    
    public function createPost($userID, $content, $media = null) {
        $query = "INSERT INTO posts (user_id, post_description, media, timestamp)
                  VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "iss", $userID, $content, $media);
        return mysqli_stmt_execute($stmt);
    }

   
    public function getAllPosts() {
        $query = "SELECT posts.*, users.name as userName
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

    public function getPostsByUser($userID) {
        $query = "SELECT * FROM posts WHERE user_id = ? ORDER BY timestamp DESC";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $userID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $posts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
        return $posts;
    }
    public function deletePost($postID) {
        $query = "DELETE FROM posts WHERE post_id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $postID);
        return mysqli_stmt_execute($stmt);
    }
}
?>
