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
$fullName = htmlspecialchars($fullName);

// Handle form submission for uploading grades
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload') {
    $teacher_id = $_POST['teacher_id'];
    $quiz = $_POST['quiz'];
    $midterm = $_POST['midterm'];
    $assignment = $_POST['assignment'];
    $final_exam = $_POST['final_exam'];

    // Loop through each student-course combination
    foreach ($quiz as $student_id => $courses) {
        foreach ($courses as $course_id => $quiz_score) {
            $midterm_score = $midterm[$student_id][$course_id];
            $assignment_score = $assignment[$student_id][$course_id];
            $final_exam_score = $final_exam[$student_id][$course_id];

            // Validate input
            if (!is_numeric($quiz_score) || !is_numeric($midterm_score) || !is_numeric($assignment_score) || !is_numeric($final_exam_score)) {
                $error = "All fields are required and must be numeric.";
                continue;
            }

            // Calculate total mark based on weights
            $total_mark = ($quiz_score * 0.10) + ($midterm_score * 0.20) + ($assignment_score * 0.10) + ($final_exam_score * 0.60);

            // Check if the grade already exists
            $sql = "SELECT * FROM pending_grades WHERE student_id = ? AND course_id = ? AND teacher_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $student_id, $course_id, $teacher_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update existing grade
                $sql = "UPDATE pending_grades SET quiz = ?, midterm = ?, assignment = ?, final_exam = ?, total_mark = ? WHERE student_id = ? AND course_id = ? AND teacher_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ddddssss", $quiz_score, $midterm_score, $assignment_score, $final_exam_score, $total_mark, $student_id, $course_id, $teacher_id);
            } else {
                // Insert new grade
                $sql = "INSERT INTO pending_grades (student_id, course_id, quiz, midterm, assignment, final_exam, total_mark, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssddds", $student_id, $course_id, $quiz_score, $midterm_score, $assignment_score, $final_exam_score, $total_mark, $teacher_id);
            }

            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->execute();
        }
    }

    if ($stmt->affected_rows > 0) {
        $success = "Marks uploaded successfully.";
    } else {
        $error = "Failed to upload marks.";
    }

    $stmt->close();
}

// Fetch students and courses related to the teacher
$sql = "SELECT s.id AS student_id, CONCAT(s.fname, ' ', s.mname, ' ', s.lname) AS student_name, c.course_id, c.course_name
        FROM students s
        JOIN enrollments e ON s.id = e.student_id
        JOIN courses c ON e.course_id = c.course_id
        JOIN teacher_courses tc ON c.course_id = tc.course_id
        JOIN teachers t ON tc.teacher_id = t.id
        WHERE t.username = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$enrollments = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teacher.css">
    <title>Upload Student Marks</title>
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

        .upload-button {
            background-color: #00ADB5;
            color: #222831;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .upload-button.uploaded {
            background-color: #00A3A5;
            color: #EEEEEE;
        }

        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .message.success {
            background-color: #00ADB5;
            color: #222831;
        }

        .message.error {
            background-color: #FF6F6F;
            color: #222831;
        }
    </style>
</head>
<body>
    <?php include 'teacher_sidebar.php'; ?>

    <div class="content">
        <h1>Upload Student Marks</h1>

        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="teacher_id" value="<?php echo htmlspecialchars($user); ?>">
            <table>
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Quiz</th>
                        <th>Midterm</th>
                        <th>Assignment</th>
                        <th>Final Exam</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($enrollment['course_id']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                            <td><input type="number" name="quiz[<?php echo htmlspecialchars($enrollment['student_id']); ?>][<?php echo htmlspecialchars($enrollment['course_id']); ?>]" step="0.01" required></td>
                            <td><input type="number" name="midterm[<?php echo htmlspecialchars($enrollment['student_id']); ?>][<?php echo htmlspecialchars($enrollment['course_id']); ?>]" step="0.01" required></td>
                            <td><input type="number" name="assignment[<?php echo htmlspecialchars($enrollment['student_id']); ?>][<?php echo htmlspecialchars($enrollment['course_id']); ?>]" step="0.01" required></td>
                            <td><input type="number" name="final_exam[<?php echo htmlspecialchars($enrollment['student_id']); ?>][<?php echo htmlspecialchars($enrollment['course_id']); ?>]" step="0.01" required></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" name="action" value="upload" class="upload-button">Upload Marks</button>
        </form>
    </div>
</body>
</html>