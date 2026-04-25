<?php
session_start();
require_once "../DBController.php";
require_once "../classes/User.php";
require_once "../classes/Profile.php";
require_once "../classes/Skill.php";

include 'header.php';

$db = new DBController();
if (!$db->openConnection()) {
    die("❌ Could not connect to database.");
}

$connection = $db->connection;
if (!isset($_SESSION['user'])) {
  header("Location: register.php");
  exit;
}
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : ['userID' => 1, 'name' => 'Test User'];
$loggedInUserID = $currentUser['userID'];

$viewUserID = isset($_GET['user']) ? intval($_GET['user']) : $loggedInUserID;

$user    = new User($connection);
$profile = new Profile($connection);
$skill   = new Skill($connection);

$userInfo = $user->getUserByID($viewUserID);
if (!$userInfo) {
    die("❌ User not found with ID: $viewUserID");
}

$profileInfo = $profile->getProfileByUserID($viewUserID);
if (!$profileInfo && $viewUserID === $loggedInUserID) {
    $profile->createProfile($loggedInUserID);
    $profileInfo = $profile->getProfileByUserID($loggedInUserID);
}

$profileID = $profileInfo['profile_id'];
$skills    = $skill->getSkillsByProfileID($profileID);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['endorse_skillID'])) {
    $skillID = intval($_POST['endorse_skillID']);
    $skill->endorseSkill($skillID);
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    if (!empty($_GET)) $url .= '?' . http_build_query($_GET);
    header("Location: $url");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASMMA - Profile</title>
    <link rel="stylesheet" href="../style/profilePage_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        .message {
            padding: 10px 20px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .close-modal {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-title {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
        }

        .modal-submit {
            background-color: #007b8f;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            align-self: flex-end;
        }

        .modal-submit:hover {
            background-color: #007b8f;
        }

        .edit-section {
            background: #0b2239;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            padding: 4px 10px;
            font-size: 15px;
            color:rgb(255, 255, 255);
            margin-left: 8px;
            transition: background 0.2s, color 0.2s, border 0.2s;
            display: inline-flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }

        .edit-section:hover {
            background: #e0e8ff;
            color: #003399;
            border: 1px solid #4a6cf7;
        }

        .edit-section span {
            font-size: 14px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-title {
            margin-bottom: 20px;
            color: #333;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .modal-submit {
            background-color: #007b8f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s;
        }

        .modal-submit:hover {
            background-color: #0b2239;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #333;
        }

        .profile-button {
            font-family: 'Poppins', sans-serif;
            color: white;
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #0b2239;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .profile-button:hover {
            background-color: #007b8f;
        }

        .profile-label {
            font-weight: 500;
            color: #333;
            margin-right: 4px;
        }

        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .card-body {
            padding: 20px;
        }

        .profile-header {
            position: relative;
        }

        .profile-cover {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        .change-cover {
            font-family: 'Poppins', sans-serif;
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #0b2239;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .profile-picture-container {
            position: relative;
            text-align: center;
            margin-bottom: 20px;
            z-index: 10;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            background: #f0f0f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: #666;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .profile-picture img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-info {
            text-align: center;
            padding: 0 20px 20px;
        }

        .profile-name {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #333;
        }

        .profile-address {
            font-family: 'Poppins', sans-serif;
            color: #666;
            margin-bottom: 10px;
        }

        .about-text p {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #444;
            margin: 0;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-tag {
            background: #f0f4ff;
            color: #0b2239;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .skill-input-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .remove-skill {
            font-family: 'Poppins', sans-serif;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        .add-skill {
            font-family: 'Poppins', sans-serif;
            background: #0b2239;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
  <div class="main-container">
    <?php
    if (isset($_GET['success'])) {
        $message = '';
        switch ($_GET['success']) {
            case 'profile_updated':
                $message = 'Profile picture updated successfully!';
                break;
            case 'cover_updated':
                $message = 'Cover image updated successfully!';
                break;
            case 'bio_updated':
                $message = 'About section updated successfully!';
                break;
            case 'address_updated':
                $message = 'Address updated successfully!';
                break;
            case 'experience_updated':
                $message = 'Education & Experience updated successfully!';
                break;
            case 'skills_updated':
                $message = 'Skills updated successfully!';
                break;
        }
        if ($message) {
            echo '<div class="message success">' . htmlspecialchars($message) . '</div>';
        }
    }
    if (isset($_GET['error'])) {
        $message = '';
        switch ($_GET['error']) {
            case 'invalid_file_type':
                $message = 'Please upload only JPG, PNG or GIF images.';
                break;
            case 'file_too_large':
                $message = 'File size should be less than 5MB.';
                break;
            case 'upload_failed':
                $message = 'Failed to upload the image. Please try again.';
                break;
            case 'empty':
                $message = 'Please fill in the required fields.';
                break;
            case 'failed':
                $message = 'Something went wrong. Please try again.';
                break;
        }
        if ($message) {
            echo '<div class="message error">' . htmlspecialchars($message) . '</div>';
        }
    }
    ?>
    <div class="card profile-header">
      <div class="profile-cover">
        <?php if (!empty($profileInfo['cover_pic'])): ?>
          <img src="../uploads/cover_pics/<?= htmlspecialchars($profileInfo['cover_pic']) ?>" 
               alt="Cover Picture" style="width: 100%; height: 100%; object-fit: cover;">
        <?php endif; ?>
        <?php if ($viewUserID === $loggedInUserID): ?>
          <form method="POST" action="../profile_handler/upload_cover_pic.php" enctype="multipart/form-data" style="position: absolute; bottom: 10px; right: 10px;">
            <input type="file" name="cover_pic" accept="image/*" style="display: none;" onchange="this.form.submit()">
            <button type="button" class="change-cover" onclick="this.previousElementSibling.click()"><span>📷</span> Change Cover</button>
          </form>
        <?php endif; ?>
      </div>

      <div class="profile-picture-container">
        <?php if (!empty($profileInfo['profile_pic'])): ?>
          <img src="../uploads/profile_pics/<?= htmlspecialchars($profileInfo['profile_pic']) ?>"
               alt="Profile Picture"
               class="profile-picture">
        <?php else: ?>
          <div class="profile-picture">
            <?= strtoupper(substr($userInfo['name'],0,1)) ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ($viewUserID === $loggedInUserID): ?>
        <form id="profilePicForm"
              method="POST"
              action="../profile_handler/upload_profile_pic.php"
              enctype="multipart/form-data"
              style="text-align: center; margin-top: 10px;">
          <input type="file"
                 id="profilePicInput"
                 name="profile_pic"
                 accept="image/*"
                 style="display: none;">
          <button type="button" class="profile-button secondary-button" style="margin: 0 auto;">Change Photo</button>
        </form>
      <?php endif; ?>
            
      <div class="profile-info">
          <h1 class="profile-name"><?php echo htmlspecialchars($userInfo['name']); ?></h1>
          <div class="profile-address">
              <span class="profile-label">Address:</span> "<?php echo htmlspecialchars($profileInfo['address'] ?? ''); ?>"
              <?php if ($viewUserID === $loggedInUserID): ?>
                  <button class="edit-section" data-modal="addressModal">
                      <span>✏</span>
                  </button>
              <?php endif; ?>
          </div>
      </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">About</div>
            <?php if ($viewUserID === $loggedInUserID): ?>
                <button class="edit-section">
                    <span>✏</span> Edit
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="about-text">
                <p>
                    <?php echo !empty($profileInfo['bio']) ? htmlspecialchars($profileInfo['bio']) : "There is no bio..."; ?>
                </p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Education & Experience</div>
            <?php if ($viewUserID === $loggedInUserID): ?>
                <button class="edit-section">
                    <span>✏</span> Edit
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="about-text">
                <p>
                    <?php
                    echo !empty($profileInfo['education_and_experience'])
                        ? nl2br(htmlspecialchars($profileInfo['education_and_experience']))
                        : "No education or experience information provided yet.";
                    ?>
                </p>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <div class="card-title">Skills</div>
            <?php if ($viewUserID === $loggedInUserID): ?>
                <button class="edit-section">
                    <span>✏</span> Edit
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="skills-list">
                <?php if (!empty($skills)): ?>
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-tag" style="display: flex; align-items: center; gap: 8px;">
                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                            <span style="background: #e0e8ff; color: #4a6cf7; border-radius: 10px; padding: 2px 8px; font-size: 12px;">
                                <?php echo (int)$skill['endorsement_count']; ?> 👍
                            </span>
                            <?php if ($viewUserID !== $loggedInUserID): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="endorse_skillID" value="<?php echo (int)$skill['skill_id']; ?>">
                                    <button type="submit" class="endorse-btn" style="margin-left: 5px; background: #4a6cf7; color: white; border: none; border-radius: 10px; padding: 2px 8px; cursor: pointer; font-size: 12px;">
                                        Endorse
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>No skills added yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
  </div>

  <div id="aboutModal" class="modal">
      <div class="modal-content">
          <span class="close-modal">&times;</span>
          <h3 class="modal-title">Edit About</h3>
          <form class="modal-form" action="../profile_handler/update_profile.php" method="POST">
              <input type="hidden" name="section" value="bio">
              <textarea class="modal-textarea" name="content" placeholder="Write about yourself..."><?php echo htmlspecialchars($profileInfo['bio'] ?? ''); ?></textarea>
              <button type="submit" class="modal-submit">Save Changes</button>
          </form>
      </div>
  </div>

  <div id="experienceModal" class="modal">
      <div class="modal-content">
          <span class="close-modal">&times;</span>
          <h3 class="modal-title">Edit Education & Experience</h3>
          <form class="modal-form" action="../profile_handler/update_profile.php" method="POST">
              <input type="hidden" name="section" value="education_and_experience">
              <textarea class="modal-textarea" name="content" placeholder="Add your education and experience..."><?php echo htmlspecialchars($profileInfo['education_and_experience'] ?? ''); ?></textarea>
              <button type="submit" class="modal-submit">Save Changes</button>
          </form>
      </div>
  </div>

  <div id="skillsModal" class="modal">
      <div class="modal-content">
          <span class="close-modal">&times;</span>
          <h3 class="modal-title">Edit Skills</h3>
          <form class="modal-form" action="../profile_handler/update_profile.php" method="POST">
              <input type="hidden" name="section" value="skills">
              <div class="skills-input-container">
                  <?php if (!empty($skills)): ?>
                      <?php foreach ($skills as $skill): ?>
                          <div class="skill-input-row">
                              <input type="text" class="modal-input" name="skills[]" value="<?php echo htmlspecialchars($skill['skill_name']); ?>">
                              <button type="button" class="remove-skill">X</button>
                          </div>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </div>
              <button type="button" class="add-skill">+ Add Skill</button>
              <button type="submit" class="modal-submit">Save Changes</button>
          </form>
      </div>
  </div>

  <div id="addressModal" class="modal">
      <div class="modal-content">
          <span class="close-modal">&times;</span>
          <h3 class="modal-title">Edit Address</h3>
          <form class="modal-form" action="../profile_handler/update_profile.php" method="POST">
              <input type="hidden" name="section" value="address">
              <input type="text" class="modal-input" name="content" placeholder="Enter your address" value="<?php echo htmlspecialchars($profileInfo['address'] ?? ''); ?>">
              <button type="submit" class="modal-submit">Save Changes</button>
          </form>
      </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const btn  = document.querySelector('.profile-button');
    const inp  = document.getElementById('profilePicInput');
    const form = document.getElementById('profilePicForm');
    if(!btn || !inp || !form) return;

    btn.addEventListener('click', ()=> inp.click() );
    inp.addEventListener('change', ()=>{
      if(inp.files.length) form.submit();
    });

    const editButtons = document.querySelectorAll('.edit-section');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            let modalId;
            const card = this.closest('.card');
            const cardTitleElem = card ? card.querySelector('.card-title') : null;
            const cardTitle = cardTitleElem ? cardTitleElem.textContent.toLowerCase() : null;

            if (cardTitle === 'about') {
                modalId = 'aboutModal';
            } else if (cardTitle === 'education & experience') {
                modalId = 'experienceModal';
            } else if (cardTitle === 'skills') {
                modalId = 'skillsModal';
            } else {
                modalId = this.getAttribute('data-modal');
            }

            if (modalId) {
                document.getElementById(modalId).style.display = 'block';
            }
        });
    });

    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    const addSkillButton = document.querySelector('.add-skill');
    const skillsContainer = document.querySelector('.skills-input-container');
    
    if (addSkillButton && skillsContainer) {
        addSkillButton.addEventListener('click', function() {
            const skillRow = document.createElement('div');
            skillRow.className = 'skill-input-row';
            skillRow.innerHTML = `
                <input type="text" class="modal-input" name="skills[]" placeholder="Enter skill">
                <button type="button" class="remove-skill">X</button>
            `;
            skillsContainer.appendChild(skillRow);
        });
        skillsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-skill')) {
                e.target.closest('.skill-input-row').remove();
            }
        });
    }
  });
  </script>
</body>
</html>