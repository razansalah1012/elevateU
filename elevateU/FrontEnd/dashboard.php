<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: register.php");
    exit;
}
include 'header.php';

require_once "../DBController.php";
require_once "../classes/User.php";
require_once "../classes/Post.php";
require_once "../classes/Profile.php";

$db = new DBController();
if (!$db->openConnection()) {
    die("❌ Could not connect to database.");
}
$connection = $db->connection;
$currentUser = $_SESSION['user'];
$userID = $currentUser['userID'];

$user = new User($connection);
$post = new Post($connection);
$profile = new Profile($connection);
//$connObj = new Connection($connection);

$userInfo = $user->getUserById($userID);
$initial = strtoupper($userInfo['name'][0]);
$profileInfo = $profile->getProfileByUserID($userID);

$posts = $post->getAllPosts();
//$suggestions = $connObj->getConnections($userID); // I might want to modify this logic later

$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    $message = 'Post created successfully!';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = 'Failed to create post. Please try again.';
    $messageType = 'error';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Asmma – Dashboard</title>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ElevateU – Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Open Sans', sans-serif;
      background-color: #f0f4f8;
      color: #1f2937;
      line-height: 1.6;
      padding: 20px;
    }

    .main-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      gap: 30px;
    }

    .message {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 15px;
      border: 1px solid;
    }

    .message.success {
      background-color: #d1fae5;
      color: #065f46;
      border-color: #10b981;
    }

    .message.error {
      background-color: #fee2e2;
      color:rgb(117, 29, 29);
      border-color: #ef4444;
    }

    .card {
      background-color: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      padding: 16px 20px;
      border-bottom: 1px solid #e5e7eb;
      font-weight: 600;
      background-color: #f9fafb;
    }

    .card-body {
      padding: 20px;
    }

    .profile-sidebar {
      position: sticky;
      top: 20px;
      width: 280px;
      flex-shrink: 0;
    }

    .feed {
      flex: 1;
    }

    .profile-card {
      text-align: center;
    }

    .profile-cover {
      height: 90px;
      background-color: #0b2239;
    }

    .profile-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color:rgba(0, 186, 161, 0.58);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      font-weight: 700;
      margin: -40px auto 15px;
      border: 4px solid white;
    }

    .profile-name {
      font-size: 20px;
      font-weight: 600;
      color:rgb(0, 53, 51);
    }

    .profile-headline {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 20px;
      padding: 6px 12px;
      background-color: #e0f2fe;
      border-radius: 6px;
      display: inline-block;
    }

    .profile-bio {
      font-size: 14px;
      color: #374151;
      padding: 20px;
      border-top: 1px solid #e5e7eb;
      background-color: #f9fafb;
    }

    .post-form {
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .post-input-row {
      display: flex;
      gap: 12px;
    }

    .post-avatar {
      width: 42px;
      height: 42px;
      background-color: rgba(0, 186, 161, 0.58);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    .post-input {
      flex: 1;
      padding: 12px 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 14px;
      background-color: #fff;
      resize: vertical;
    }

    .post-input:focus {
      border-color: #0b2239;
      outline: none;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .file-input {
      padding: 10px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 14px;
      background-color: #f9fafb;
    }

    .post-button {
      background-color: #0b2239;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }

    .post-button:hover {
      background-color:rgb(150, 219, 213);
    }

    .post-header, .post-content, .post-actions {
      padding: 16px 20px;
    }

    .post {
      margin-top: 20px;
    }

    .post-info {
      margin-left: 12px;
    }

    .post-author {
      font-weight: 600;
      color: #111827;
    }

    .post-meta {
      font-size: 12px;
      color: #6b7280;
    }

    .post-content p {
      margin: 10px 0;
    }

    .like-button {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background-color: white;
      color: #374151;
      cursor: pointer;
    }

    .like-button:hover {
      background-color: #e0f2fe;
    }

    .like-button.active {
      background-color:rgb(0, 108, 125);
      color: white;
    }
    .file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    background-color: #0b2239;
    padding: 6px 16px;
    font-size: 14px;
    cursor: pointer;
    color:rgb(255, 255, 255);
    transition: background-color 0.2s;
    font-family: 'Poppins', sans-serif;
    }

    .file-input-wrapper:hover {
    background-color: rgb(0, 108, 125);
    }

    .file-input-wrapper input[type="file"] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    }
    .like-button {
    background: none;
    border: none;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    color: #444;
    font-size: 16px;
    transition: color 0.3s ease;
    }

    .like-button .heart {
    width: 24px;
    height: 24px;
    fill: #bbb;
    transition: fill 0.3s ease, transform 0.2s ease;
    margin-right: 6px;
    }

    .like-button.liked .heart {
    fill: #fa314a;
    transform: scale(1.2);
    }

    .like-button.liked {
    color: #fa314a;
    }

    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
        padding: 10px;
      }

      .profile-sidebar {
        width: 100%;
      }
    }

  </style>
</head>
<body>
<div class="main-container">

    <div class="profile-sidebar">
        <div class="card profile-card">
            <div class="profile-cover"></div>
            <div class="profile-cover">
                <?php if (!empty($profileInfo['cover_pic'])): ?>
                    <img src="../uploads/cover_pics/<?= htmlspecialchars($profileInfo['cover_pic']) ?>" 
                         alt="Cover Picture" style="width: 100%; height: 80px; object-fit: cover;">
                <?php endif; ?>
            </div>
            <div class="profile-avatar" style="background: none; padding: 0;">
                <?php if (!empty($profileInfo['profile_pic'])): ?>
                    <img src="../uploads/profile_pics/<?= htmlspecialchars($profileInfo['profile_pic']) ?>"
                         alt="Profile Picture"
                         style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600;">
                        <?= $initial ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="profile-name"><?= htmlspecialchars($userInfo['name']) ?></div>
                <div class="profile-headline"><?= $userInfo['role'] === 'employer' ? "Job Poster" : "Job Seeker" ?></div>

                <div class="profile-bio">
                    <?= htmlspecialchars($user->getBio($userID)) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="feed">

        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="../back_script/create_post.php" enctype="multipart/form-data" class="post-form">
                <div class="create-post">
                    <div class="post-input-row">
                        <div class="post-avatar"><?= $initial ?></div>
                        <textarea class="post-input" name="content" placeholder="What's on your mind?" required></textarea>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 10px;">
                        <label class="file-input-wrapper">
                        Choose File
                        <input type="file" name="media" accept="image/*,video/*">
                        </label>
                        <button type="submit" class="post-button">Post</button>
                    </div>
                </div>
            </form>
        </div>

        <?php foreach ($posts as $p): ?>
            <?php $postProfile = $profile->getProfileByUserID($p['user_id']); ?>
            <div class="card post">
                <div class="post-header">
                    <div class="post-avatar">
                        <?php if (!empty($postProfile['profile_pic'])): ?>
                            <img src="../uploads/profile_pics/<?= htmlspecialchars($postProfile['profile_pic']) ?>" alt="Profile Picture" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper($p['userName'][0]) ?>
                        <?php endif; ?>
                    </div>
                    <div class="post-info">
                        <div class="post-author">
                            <a href="profilePage.php?user=<?= urlencode($p['user_id']) ?>">
                                <?= htmlspecialchars($p['userName']) ?>
                            </a>
                        </div>
                        <div class="post-meta"><?= date("F j, Y", strtotime($p['timestamp'])) ?></div>
                    </div>
                </div>
                <div class="post-content">
                    <p><?= htmlspecialchars($p['post_description']) ?></p>
                    <?php if ($p['media']): ?>
                        <div><img src="../uploads/<?= htmlspecialchars($p['media']) ?>" width="100%" style="border-radius:10px;"></div>
                    <?php endif; ?>
                </div>
                <div>
                <button class="like-button" onclick="toggleLike(this)">
                <svg class="heart" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                            2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09
                            C13.09 3.81 14.76 3 16.5 3
                            19.58 3 22 5.42 22 8.5
                            c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span class="like-count">0</span>
                </button>
                </div>     
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function toggleLike(btn) {
  btn.classList.toggle('liked');
  const countSpan = btn.querySelector('.like-count');
  let count = parseInt(countSpan.textContent);
  countSpan.textContent = btn.classList.contains('liked') ? count + 1 : count - 1;
}
</script>


</body>
</html>
