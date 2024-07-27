<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] != 'student') {
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

// Fetch student name
$user = $_SESSION['username'];
$sql = "SELECT fname, mname, lname FROM user WHERE username = ?";
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
    <link rel="stylesheet" href="student.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Student Dashboard</title>
</head>
<body>
    <header class="header">
        <a href="#">Student Dashboard</a>
        <div class="header-info">
            <span class="student-name">Welcome, <?php echo $fullName; ?>!</span>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <aside>
        <ul>
            <li><a href="#">My Courses</a></li>
            <li><a href="#">My Results</a></li>
            <li><a href="#">Profile</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
    </aside>

    <div class="content">
        <h1>Welcome to Your Dashboard</h1>
        <p>Here you can view your courses, results, and manage your profile settings.</p>
    </div>
</body>
</html>
