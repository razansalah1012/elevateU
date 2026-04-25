<?php
session_start();
require_once "../DBController.php";
require_once "../classes/Application.php";

$db = new DBController();
$db->openConnection();
$conn = $db->connection;

$applicationObj = new Application($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobID = $_POST['jobID'] ?? '';
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $applicationDescription = $_POST['applicationDescription'] ?? '';
    $userID = $_SESSION['user']['userID'];
    
    $resumeFile = '';
    
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $fileName = time() . '_' . $_FILES['resume']['name'];
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)) {
            $resumeFile = $fileName;
        }
    }

    $result = $applicationObj->applyToJob($jobID, $userID, $resumeFile, $applicationDescription);
    
    if ($result) {
        header("Location: ../FrontEnd/jobs.php?applied=1");
        exit;
    } else {
        header("Location: ../FrontEnd/jobs.php?applied=0");
        exit;
    }
} else {
    header("Location: ../FrontEnd/jobs.php");
    exit;
}
?> 