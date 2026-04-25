<?php
session_start();
require_once "../DBController.php";
require_once "../classes/Profile.php";

$db = new DBController();
if (!$db->openConnection()) {
    die("❌ Could not connect to database.");
}

$connection = $db->connection;

$userID = isset($_SESSION['user']['userID']) ? $_SESSION['user']['userID'] : 1;

$profile = new Profile($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cover_pic'])) {
    $file = $_FILES['cover_pic'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../FrontEnd/profilePage.php?error=upload_failed");
        exit;
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        header("Location: ../FrontEnd/profilePage.php?error=file_too_large");
        exit;
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        header("Location: ../FrontEnd/profilePage.php?error=invalid_file_type");
        exit;
    }

    $uploadDir = '../uploads/cover_pics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'cover_' . $userID . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
 
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $result = $profile->updateCoverPicture($userID, $fileName);
        if ($result) {
            header("Location: ../FrontEnd/profilePage.php?success=cover_updated");
        } else {
            header("Location: ../FrontEnd/profilePage.php?error=failed");
        }
    } else {
        header("Location: ../FrontEnd/profilePage.php?error=upload_failed");
    }
    exit;
} else {
    header("Location: ../FrontEnd/profilePage.php");
    exit;
}
?> 