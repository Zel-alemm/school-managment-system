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
$username = "root";
$password = "";
$dbname = "lumamedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student name
$user = $_SESSION['username'];
$sql = "SELECT fname, mname, lname FROM students WHERE username = ?";
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

// Fetch courses and teacher names for the student from the student_teacher_assignments table
$sql = "SELECT courses.course_id, courses.course_name, 
               CONCAT(teachers.fname, ' ', teachers.mname, ' ', teachers.lname) AS teacher_fullname
        FROM student_teacher_assignments
        JOIN courses ON student_teacher_assignments.course_id = courses.course_id
        JOIN teachers ON student_teacher_assignments.teacher_id = teachers.id
        WHERE student_teacher_assignments.student_id = (SELECT id FROM students WHERE username = ?)";
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
    <link rel="stylesheet" href="student.css">
    <title>Student Dashboard - My Courses</title>
    <style>
        table {
            width: 80%; /* Adjust the width to make it smaller */
            margin: 20px auto; /* Center the table and add margin */
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #222831;
        }
        th, td {
            padding: 8px; /* Reduce padding to make the table more compact */
            text-align: left;
        }
        th {
            background-color: #00ADB5;
            color: #EEEEEE;
            font-size: 14px; /* Smaller font size for header */
        }
        td {
            font-size: 12px; /* Smaller font size for table cells */
        }
        tr:nth-child(even) {
            background-color: #393E46;
        }
        tr:nth-child(odd) {
            background-color: #393E46;
        }
    </style>
</head>
<body>
<?php include 'student_sidebar.php'; ?>

    <div class="content">
        <h1>My Courses</h1>
        <?php if (empty($courses)): ?>
            <p>You are not enrolled in any courses.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Teacher Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['teacher_fullname']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
