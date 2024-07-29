<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

// Debugging output for student ID
// if (!isset($_SESSION['username'])) {
//     die("Student ID is not set in session. Ensure login sets the session ID correctly.");
// }

$student_id = $_SESSION['username']; // Fetch student ID from session

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
// Fetch student results
$sql = "SELECT g.course_id, c.course_name, g.quiz, g.midterm, g.assignment, g.final_exam, g.total_mark, g.grade
        FROM grades g
        JOIN courses c ON g.course_id = c.course_id
        WHERE g.student_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$results = $result->fetch_all(MYSQLI_ASSOC);

if (empty($results)) {
    echo "No results found for student ID: $student_id";
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
    <title>My Results</title>
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
        }

        table, th, td {
            border: 1px solid #393E46;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #00ADB5;
            color: #EEEEEE;
        }

        td {
            background-color: #393E46;
        }
    </style>
</head>
<body>
<?php include 'student_sidebar.php'; ?> <!-- Ensure you include your sidebar -->

    <div class="content">
        <h1>My Results</h1>

        <table>
            <thead>
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Quiz</th>
                    <th>Midterm</th>
                    <th>Assignment</th>
                    <th>Final Exam</th>
                    <th>Total Mark</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($result['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['quiz']); ?></td>
                            <td><?php echo htmlspecialchars($result['midterm']); ?></td>
                            <td><?php echo htmlspecialchars($result['assignment']); ?></td>
                            <td><?php echo htmlspecialchars($result['final_exam']); ?></td>
                            <td><?php echo htmlspecialchars($result['total_mark']); ?></td>
                            <td><?php echo htmlspecialchars($result['grade']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No results available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
