<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] != 'teacher') {
    header("location:login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "lumamedb"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch teacher name
$user = $_SESSION['username'];
$sql = "SELECT fname, mname, lname FROM teachers WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($fname, $mname, $lname);
$stmt->fetch();
$stmt->close();
$conn->close();

// Combine names
$fullName = trim($fname . ' ' . $mname . ' ' . $lname);

// Sanitize output
$fullName = htmlspecialchars($fullName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teacher.css">
     <title>teacher Dashboard</title>
</head>
<body>
<?php include 'teacher_sidebar.php'; ?>

    <div class="content">
        <h1>Welcome to Your Dashboard</h1>
        <p>Here you can view your courses, results, and manage your profile settings.</p>
    </div>
</body>
</html>
