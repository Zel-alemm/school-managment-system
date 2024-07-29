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

// Fetch students taught by the teacher
$sql = "SELECT DISTINCT students.id, students.fname, students.mname, students.lname 
        FROM enrollments
        JOIN courses ON enrollments.course_id = courses.course_id
        JOIN teacher_courses ON courses.course_id = teacher_courses.course_id
        JOIN teachers ON teacher_courses.teacher_id = teachers.id
        JOIN students ON enrollments.student_id = students.id
        WHERE teachers.username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
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
    <title>Students Taught - Teacher Dashboard</title>
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


    <?php include 'teacher_sidebar.php'; ?>

    <div class="content">
        <h1>Students Taught</h1>
        <?php if (empty($students)): ?>
            <p>You are not teaching any students.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                            <td><?php echo htmlspecialchars($student['fname']); ?></td>
                            <td><?php echo htmlspecialchars($student['mname']); ?></td>
                            <td><?php echo htmlspecialchars($student['lname']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
