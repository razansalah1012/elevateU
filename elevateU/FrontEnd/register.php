<?php
require_once("../DBController.php");
require_once("../classes/User.php");
require_once("../classes/Profile.php");

session_start();

$db = new DBController();
$db->openConnection();
$userObj = new User($db->connection);
$profileObj = new Profile($db->connection);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = $_POST['loginEmail'];
    $password = $_POST['loginPassword'];

    $user = $userObj->login($email, $password);
    if ($user) {
        $role = $user['role'];
        if ($role === "admin") {
            
            $_SESSION['user'] = $user;
            header("Location: ../FrontEnd/admindashboard.php");
            exit;
        }if ($role === "employer") {
            
          $_SESSION['user'] = $user;
          header("Location: ../FrontEnd/jobs2.php");
          exit;
      } else {
            $_SESSION['user'] = $user;
            header("Location: ../FrontEnd/dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid credentials or banned account.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $name = $_POST['registerName'];
    $email = $_POST['registerEmail'];
    $password = $_POST['registerPassword'];
    $repeatPassword = $_POST['registerRepeatPassword'];
    $role = $_POST['registerRole'];

    if ($password !== $repeatPassword) {
        $error = "Passwords do not match!";
    } else {
        $registered = $userObj->register($name, $email, $password, $role);
        if ($registered) {
            $user = $userObj->login($email, $password);
            if ($user) {
              $role = $user['role'];
      
              if ($role === "admin") {
                  
                  $_SESSION['user'] = $user;
                  header("Location: ../FrontEnd/admindashboard.php");
                  exit;
              }if ($role === "employer") {
            
                $_SESSION['user'] = $user;
                header("Location: ../FrontEnd/jobs2.php");
                exit;
            } else {
                  $_SESSION['user'] = $user;
                  header("Location: ../FrontEnd/dashboard.php");
                  exit;
              }
            }
        } else {
            $error = "❌ Failed to register user (maybe already exists?)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>elevateU – Login / Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Poppins:wght@500;700&display=swap" rel="stylesheet">

  <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Poppins', sans-serif;
    background-color: #f0f4f8;
    color: #5a5a5a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }

  .container {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 40px;
    max-width: 460px;
    width: 100%;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid #ddd;
  }

  .brand-title {
    font-family: 'Poppins', sans-serif;
    text-align: center;
    font-size: 32px;
    font-weight: 700;
    color: #0b2239;
    margin-bottom: 30px;
    letter-spacing: 1px;
  }

  .tabs {
    display: flex;
    margin-bottom: 30px;
    border-bottom: 1px solid #e3e3e3;
  }

  .tab-btn {
    flex: 1;
    padding: 12px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.2s ease;
    border-bottom: 3px solid transparent;
  }

  .tab-btn.active {
    color: #00bfa6;
    border-bottom-color: #00bfa6;
  }

  .tab-btn:hover {
    color: #00bfa6;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #0b2239;
    font-size: 14px;
  }

  .form-control,
  .form-select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    background-color: white;
    transition: border-color 0.2s ease;
  }

  .form-control:focus,
  .form-select:focus {
    outline: none;
    border-color: #00bfa6;
    box-shadow: 0 0 0 2px rgba(0, 191, 166, 0.1);
  }

  .btn {
    width: 100%;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .btn-primary {
    background-color: #00bfa6;
    color: #fff;
  }

  .btn-primary:hover {
    background-color: #00a892;
  }

  .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
    font-size: 14px;
  }

  @media (max-width: 480px) {
    body {
      padding: 10px;
    }

    .container {
      padding: 30px 20px;
    }

    .brand-title {
      font-size: 24px;
      margin-bottom: 25px;
    }
  }

  </style>
</head>
<body>
  <div class="container">
    <div class="brand-title">ElevateU</div>
    
    <div class="tabs">
      <button class="tab-btn active" data-tab="login">Login</button>
      <button class="tab-btn" data-tab="register">Register</button>
    </div>
    <?php if (isset($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="tab-content active" id="login">
      <form method="POST">
        <input type="hidden" name="login" value="1" />
        
        <div class="form-group">
          <label class="form-label" for="loginEmail">Email</label>
          <input type="email" name="loginEmail" id="loginEmail" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="loginPassword">Password</label>
          <input type="password" name="loginPassword" id="loginPassword" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary">Sign in</button>
      </form>
    </div>
    <div class="tab-content" id="register">
      <form method="POST">
        <input type="hidden" name="register" value="1" />
        
        <div class="form-group">
          <label class="form-label" for="registerName">Full Name</label>
          <input type="text" name="registerName" id="registerName" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="registerEmail">Email</label>
          <input type="email" name="registerEmail" id="registerEmail" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="registerPassword">Password</label>
          <input type="password" name="registerPassword" id="registerPassword" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="registerRepeatPassword">Repeat Password</label>
          <input type="password" name="registerRepeatPassword" id="registerRepeatPassword" class="form-control" required />
        </div>

        <div class="form-group">
          <label class="form-label" for="registerRole">Register as:</label>
          <select class="form-select" name="registerRole" id="registerRole" required>
            <option value="student">Student – Find Jobs</option>
            <option value="employer">Employer – Post Jobs</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const tabButtons = document.querySelectorAll('.tab-btn');
      const tabContents = document.querySelectorAll('.tab-content');

      tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          const targetTab = this.getAttribute('data-tab');
          
          tabButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          
          tabContents.forEach(content => content.classList.remove('active'));
          document.getElementById(targetTab).classList.add('active');
        });
      });
    });
  </script>
</body>
</html>
