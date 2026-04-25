<?php

?>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">

<style>
.navbar {
    font-family: 'Poppins', sans-serif;
    background: #007b8f;
    color: white;
    padding: 0 40px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-radius: 15px; 
}

.navbar .nav-logo {
    font-weight: 700;
    font-size: 24px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.navbar .nav-links {
    display: flex;
    gap: 30px;
    align-items: center;
}

.navbar .nav-link {
    color:rgb(227, 255, 243);
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
    transition: color 0.2s ease;
    position: relative;
}

.navbar .nav-link:hover,
.navbar .nav-link.active {
    color: #00bfa6; 
}

.navbar .logout-btn {
    font-family: 'Poppins', sans-serif;
    background: #0b2239;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin-left: 20px;
    transition: background 0.2s ease;
}

.navbar .logout-btn:hover {
    background:rgb(125, 45, 45);
}
</style>

<div class="navbar">
    <a href="dashboard.php" class="nav-logo">ElevateU</a>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo ' active'; ?>">Dashboard</a>
        <a href="jobs.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='jobs.php') echo ' active'; ?>">Jobs</a>
        <a href="profilePage.php" class="nav-link<?php if(basename($_SERVER['PHP_SELF'])=='profilePage.php') echo ' active'; ?>">Profile</a>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="display:inline; margin:0;">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: register.php');
    exit;
}
?>
