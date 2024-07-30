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

// Combine names
$fullName = trim($fname . ' ' . $mname . ' ' . $lname);

// Sanitize output
$fullName = htmlspecialchars($fullName);

// Fetch courses and semesters the teacher is teaching
$sql = "SELECT DISTINCT courses.course_id, courses.course_name, semesters.semester_name
        FROM teacher_courses
        JOIN courses ON teacher_courses.course_id = courses.course_id
        JOIN enrollments ON courses.course_id = enrollments.course_id
        JOIN semesters ON enrollments.semester_id = semesters.semester_id
        WHERE teacher_courses.teacher_id = (SELECT id FROM teachers WHERE username = ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teacher.css">
    <title>My Courses - Teacher Dashboard</title>
    <style>
        .content h1 {
            color: #00ADB5;
        }

        .content p {
            color: #EEEEEE;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #EEEEEE;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #393E46;
            color: #EEEEEE;
        }

        tr:nth-child(even) {
            background-color: #333;
        }

        tr:hover {
            background-color: #00ADB5;
            color: #222831;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="#">Teacher Dashboard</a>
        <div class="username">Welcome, <?php echo $fullName; ?></div>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <?php include 'teacher_sidebar.php'; ?>

    <div class="content">
        <h1>My Courses</h1>
        <?php if (empty($courses)): ?>
            <p>You are not teaching any courses.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['semester_name']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
