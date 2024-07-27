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
$username = "root";
$password = "";
$dbname = "lumamedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

// Fetch courses
$courses_sql = "SELECT * FROM courses WHERE teacher_username = ?";
$courses_stmt = $conn->prepare($courses_sql);
$courses_stmt->bind_param("s", $user);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

$courses_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teacher.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Grade Students</title>
    <style>
        /* Add custom styles here */
    </style>
</head>
<body>
    <header class="header">
        <a href="#">Teacher Dashboard</a>
        <div class="header-info">
            <span class="teacher-name">Welcome, <?php echo htmlspecialchars($fname . ' ' . $mname . ' ' . $lname); ?>!</span>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <aside>
        <ul>
            <li><a href="view_courses.php">My Courses</a></li>
            <li><a href="grade_students.php">Grade Students</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
    </aside>

    <div class="content">
        <h1>Grade Students</h1>
        <form action="submit_grades.php" method="POST">
            <div class="mb-3">
                <label for="course" class="form-label">Select Course</label>
                <select id="course" name="course" class="form-select" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="student" class="form-label">Select Student</label>
                <select id="student" name="student" class="form-select" required>
                    <!-- Populate with students based on the selected course -->
                </select>
            </div>
            <div class="mb-3">
                <label for="grade" class="form-label">Grade</label>
                <input type="text" id="grade" name="grade" class="form-control" required>
            </div>
            <button type="submit" class="btn">Submit Grade</button>
        </form>
    </div>
</body>
</html>
