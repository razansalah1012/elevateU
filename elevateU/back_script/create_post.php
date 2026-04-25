<?php
session_start();
require_once("../DBController.php");
require_once("../classes/User.php");
require_once("../classes/Post.php");

if (!isset($_SESSION['user'])) {
    header("Location: ../FrontEnd/register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $db = new DBController();
    $db->openConnection();
    
    $post = new Post($db->connection);
    $userID = $_SESSION['user']['userID'];
    $content = $_POST['content'];
    $media = null;

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . $_FILES['media']['name'];
        $targetPath = $uploadDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/avi', 'video/mov'];
        if (in_array($_FILES['media']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
                $media = $fileName;
            }
        }
    }
    $result = $post->createPost($userID, $content, $media);
    
    if ($result) {
        header("Location: ../FrontEnd/dashboard.php?success=1");
        exit;
    } else {
        header("Location: ../FrontEnd/dashboard.php?error=1");
        exit;
    }
} else {
    header("Location: ../FrontEnd/dashboard.php");
    exit;
}
?> 