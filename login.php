<?php
session_start(); // Ensure this is at the very beginning of the file
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <title>Login Page</title>
  <style>
    body {
      background-color: #222831;
      color: #EEEEEE;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh; /* Full viewport height */
      margin: 0 auto;
      padding: 0 15px;
    }
    .title_deg {
      background-color: #00ADB5;
      color: #EEEEEE;
      text-align: center;
      font-weight: bold;
      width: 100%; /* Ensure full width */
      max-width: 400px; /* Match the width of the form */
      padding: 10px;
      font-size: 20px;
      margin-bottom: 0; /* Remove space between title and form */
    }
    .form-deg, .register-form {
      background-color: #393E46;
      border-radius: 8px;
      padding: 2rem;
      width: 100%;
      max-width: 400px; /* Match the width of the title */
      position: relative;
    }
    .form-img {
      text-align: center;
      margin-bottom: 1rem;
    }
    .logo {
      width: 100px;
      height: auto;
    }
    .form-floating {
      margin-bottom: 1rem;
    }
    .form-control {
      background-color: #222831;
      color: #EEEEEE;
      border: 1px solid #00ADB5;
    }
    .form-control:focus {
      border-color: #00ADB5;
      box-shadow: none;
    }
    .form-label {
      color: #EEEEEE;
    }
    .btn {
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 4px;
      text-align: center;
      font-size: 16px;
      font-weight: bold;
      display: inline-block;
      margin-right: 0.5rem;
    }
    .btn.Login-btn {
      background-color: #00ADB5;
      color: #222831;
    }
    .btn.Login-btn:hover {
      background-color: #393E46;
      color: #EEEEEE;
    }
    .btn.Register-btn {
      background-color: #393E46;
      color: #00ADB5;
    }
    .btn.Register-btn:hover {
      background-color: #00ADB5;
      color: #222831;
    }
    .btn-container {
      margin-top: 1rem;
      display: flex;
      justify-content: space-between;
    }
    .form-content1 {
      margin-bottom: 1rem;
    }
    .form-content2 {
      margin-top: 1rem;
    }
    .form-select {
      margin-top: 1rem; /* Space above the grade select field */
    }
    #registerForm {
      margin-top: 30rem;
      m;
      
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="title_deg">
      <span id="formTitle">Login Form</span>
      <h4>
        <?php
          // Display login message
          echo isset($_SESSION['loginMessage']) ? $_SESSION['loginMessage'] : '';
        ?>
      </h4>
    </div>
    <!-- Login Form -->
    <form id="loginForm" action="login_check.php" method="POST" class="form-deg">
      <div class="form-img">
        <img src="./image/logo/logo1.png" alt="logo" class="logo img-fluid">
      </div>
      <div class="form-floating pb-3">
        <input type="text" id="name" name="username" placeholder="e.g. lms1604474" class="form-control" required>
        <label for="name" class="form-label">Username</label>
      </div>
      <div class="form-floating">
        <input type="password" id="password" name="password" placeholder="e.g. 1234" class="form-control" required>
        <label for="password" class="form-label">Password</label>
      </div>
      <div class="btn-container">
        <button type="button" id="registerToggle" class="btn Register-btn">Register</button>
        <input type="submit" name="submit" value="Login" class="btn Login-btn">
      </div>
    </form>

    <!-- Register Form -->
    <form id="registerForm" action="data_check.php" method="POST" class="register-form" style="display: none;">
      <div class="title_deg">
        <span id="formTitle">Register Form</span>
        <h4>
          <?php
            // Display registration message
            echo isset($_SESSION['registermessage']) ? $_SESSION['registermessage'] : '';
          ?>
        </h4>
      </div>
      <div class="form-img">
        <img src="./image/logo/logo1.png" alt="logo" class="logo img-fluid">
      </div>
      <div class="form-floating form-content1">
        <input type="text" id="fname" name="fname" placeholder="e.g. Zelalem" class="form-control" required/>
        <label for="fname" class="form-label">First name</label>
      </div>
      <div class="form-floating form-content1">
        <input type="text" id="mname" name="mname" placeholder="e.g. Zelalem" class="form-control"/>
        <label for="mname" class="form-label">Middle name</label>
      </div>
      <div class="form-floating form-content1">
        <input type="text" id="lname" name="lname" placeholder="e.g. Zelalem" class="form-control"/>
        <label for="lname" class="form-label">Last name</label>
      </div>
      <div class="form-floating form-content1">
        <input type="number" id="age" name="age" placeholder="e.g. 19" class="form-control" required/>
        <label for="age" class="form-label">Age</label>
      </div>
      <div class="form-floating form-content1">
        <input type="email" id="email" name="email" placeholder="e.g. zel@gmail.com" class="form-control" required/>
        <label for="email" class="form-label">Email</label>
      </div>
      <div class="form-floating form-content1">
        <input type="tel" id="phone" name="phone" placeholder="e.g. +251905487849" class="form-control" required/>
        <label for="phone" class="form-label">Phone</label>
      </div>
      <div class="form-content2">
        <label for="grade" class="form-label">Grade</label>
        <select name="grade" id="grade" class="form-select" required>
          <option value="">Select Grade</option>
          <option value="9">Grade 9</option>
          <option value="10">Grade 10</option>
          <option value="11">Grade 11</option>
          <option value="12">Grade 12</option>
        </select>
      </div>
      <div class="form-content2" data-aos="fade-right" data-aos-delay="200">
                <label for="stream" class="form-label">Stream</label>
                <select name="stream" id="stream" class="form-select" required>
                    <option value="">Select Stream</option>
                    <option value="Natural">Natura Sciencel</option>
                    <option value="Social">Social Science</option>
                    <option value="Non-stream">Non-stream</option>
                </select>
            </div>
      <div class="form-floating write-text form-content2">
        <textarea name="message" id="message" style="height: 150px;" class="form-control" placeholder="write a message" required></textarea>
        <label for="message">Write a message</label>
      </div>
      <div class="btn-container">
        <button type="button" id="loginToggle" class="btn Login-btn">Login</button>
        <button type="submit" name="apply" class="btn btn-success">Submit</button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Check URL hash
      if (window.location.hash === '#registerForm') {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Register Form';
      }

      document.getElementById('registerToggle').addEventListener('click', function() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Register Form';
        window.location.hash = 'registerForm';
      });

      document.getElementById('loginToggle').addEventListener('click', function() {
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Login Form';
        window.location.hash = 'loginForm';
      });
    });
  </script>
</body>
</html>
