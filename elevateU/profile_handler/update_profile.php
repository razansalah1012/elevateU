<?php
session_start();
require_once "../DBController.php";
require_once "../classes/Profile.php";
require_once "../classes/Skill.php";

$db = new DBController();
if (!$db->openConnection()) {
    die("❌ Could not connect to database.");
}

$connection = $db->connection;
$userID = isset($_SESSION['user']['userID']) ? $_SESSION['user']['userID'] : 1;

$profile = new Profile($connection);
$skill = new Skill($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $content = $_POST['content'] ?? '';

    $profileInfo = $profile->getProfileByUserID($userID);
    if (!$profileInfo) {
        $profile->createProfile($userID);
        $profileInfo = $profile->getProfileByUserID($userID);
    }
    
    $profileID = $profileInfo['profile_id'];
    
    switch ($section) {
        case 'bio':
            if (empty($content)) {
                header("Location: ../FrontEnd/profilePage.php?error=empty");
                exit;
            }
            $result = $profile->setBio($userID, $content);
            if ($result) {
                header("Location: ../FrontEnd/profilePage.php?success=bio_updated");
            } else {
                header("Location: ../FrontEnd/profilePage.php?error=failed");
            }
            break;
            
        case 'address':
            if (empty($content)) {
                header("Location: ../FrontEnd/profilePage.php?error=empty");
                exit;
            }
            $result = $profile->setAddress($userID, $content);
            if ($result) {
                header("Location: ../FrontEnd/profilePage.php?success=address_updated");
            } else {
                header("Location: ../FrontEnd/profilePage.php?error=failed");
            }
            break;
            
        case 'education_and_experience':
            if (empty($content)) {
                header("Location: ../FrontEnd/profilePage.php?error=empty");
                exit;
            }
            $result = $profile->setEducationAndExperience($userID, $content);
            if ($result) {
                header("Location: ../FrontEnd/profilePage.php?success=experience_updated");
            } else {
                header("Location: ../FrontEnd/profilePage.php?error=failed");
            }
            break;
            
        case 'skills':
            $skills = $_POST['skills'] ?? [];
            $skills = array_filter($skills); 
            $currentSkills = $skill->getSkillsByProfileID($profileID);
            $currentSkillNames = array_column($currentSkills, 'skill_name');
            foreach ($currentSkills as $currentSkill) {
                if (!in_array($currentSkill['skill_name'], $skills)) {
                    $skill->deleteSkill($currentSkill['skill_id']);
                }
            }

            foreach ($skills as $skillName) {
                if (!in_array($skillName, $currentSkillNames)) {
                    $skill->addSkill($profileID, $skillName);
                }
            }
            
            header("Location: ../FrontEnd/profilePage.php?success=skills_updated");
            break;
            
        default:
            header("Location: ../FrontEnd/profilePage.php?error=failed");
            break;
    }
    exit;
} else {
    header("Location: ../FrontEnd/profilePage.php");
    exit;
}
?> 