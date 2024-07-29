<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] != 'admin') {
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

// Fetch the admin's full name
$username = mysqli_real_escape_string($conn, $_SESSION['username']);
$sql_user = "SELECT fname, mname, lname FROM admins WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);

if ($stmt_user) {
    $stmt_user->bind_param("s", $username);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user === false) {
        die("Error in query: " . $conn->error);
    }

    $user_info = $result_user->fetch_assoc();

    if ($user_info) {
        $fullName = htmlspecialchars($user_info['fname']) . ' ' . htmlspecialchars($user_info['mname']) . ' ' . htmlspecialchars($user_info['lname']);
    } else {
        $fullName = "Unknown User";
    }

    $stmt_user->close();
} else {
    die("Error preparing query: " . $conn->error);
}

// Handle form submission for approving grades
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $gradeLevel = $_POST['grade_level']; // This is the student's grade level

    // Fetch all pending grades for the specified grade level
    $sql_pending = "SELECT pg.*, s.grade AS student_grade
                    FROM pending_grades pg
                    JOIN students s ON pg.student_id = s.id
                    WHERE s.grade = ?";
    $stmt_pending = $conn->prepare($sql_pending);
    $stmt_pending->bind_param("i", $gradeLevel);
    $stmt_pending->execute();
    $result_pending = $stmt_pending->get_result();

    while ($row = $result_pending->fetch_assoc()) {
        $student_id = $row['student_id'];
        $course_id = $row['course_id'];
        $teacher_id = $row['teacher_id'];
        $quiz = $row['quiz'];
        $midterm = $row['midterm'];
        $assignment = $row['assignment'];
        $final_exam = $row['final_exam'];
        $pending_id = $row['id'];

        $total_mark = $quiz + $midterm + $assignment + $final_exam;

        if ($total_mark >= 90) {
            $grade = 'A';
        } elseif ($total_mark >= 80) {
            $grade = 'B';
        } elseif ($total_mark >= 70) {
            $grade = 'C';
        } elseif ($total_mark >= 60) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        $sql_insert = "INSERT INTO grades (student_id, course_id, teacher_id, quiz, midterm, assignment, final_exam, total_mark, grade) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssddddds", $student_id, $course_id, $teacher_id, $quiz, $midterm, $assignment, $final_exam, $total_mark, $grade);
        $stmt_insert->execute();

        if ($stmt_insert->affected_rows > 0) {
            $delete_sql = "DELETE FROM pending_grades WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $pending_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
        $stmt_insert->close();
    }
    $stmt_pending->close();

    $success = "Grades approved and moved to final grades for grade $gradeLevel.";
}

// Fetch pending grades for display grouped by grade levels
$sql = "SELECT pg.id AS pending_id,
               pg.course_id,
               pg.student_id,
               s.fname AS student_fname, 
               s.mname AS student_mname, 
               s.lname AS student_lname,
               pg.teacher_id,
               c.course_name,
               pg.quiz,
               pg.midterm,
               pg.assignment,
               pg.final_exam,
               pg.total_mark,
               s.grade AS student_grade
        FROM pending_grades pg
        JOIN students s ON pg.student_id = s.id
        JOIN courses c ON pg.course_id = c.course_id
        ORDER BY s.grade, pg.course_id";

$result = $conn->query($sql);
$pending_grades = [];

while ($row = $result->fetch_assoc()) {
    $pending_grades[$row['student_grade']][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard</title>
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
            margin-bottom: 20px;
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
            color: #EEEEEE;
        }

        .approve-button {
            background-color: #00ADB5;
            color: #222831;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
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
    <?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1>Approve Student Grades</h1>

        <?php if (isset($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php foreach ($pending_grades as $gradeLevel => $grades): ?>
    <h2>Grade <?php echo htmlspecialchars($gradeLevel); ?></h2>
    <table>
        <thead>
            <tr>
                <th>Course ID</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Course</th>
                <th>Quiz</th>
                <th>Midterm</th>
                <th>Assignment</th>
                <th>Final Exam</th>
                <th>Total Mark</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($grades) > 0): ?>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($grade['course_id']); ?></td>
                        <td><?php echo htmlspecialchars($grade['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($grade['student_fname'] . ' ' . $grade['student_mname'] . ' ' . $grade['student_lname']); ?></td>
                        <td><?php echo htmlspecialchars($grade['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($grade['quiz']); ?></td>
                        <td><?php echo htmlspecialchars($grade['midterm']); ?></td>
                        <td><?php echo htmlspecialchars($grade['assignment']); ?></td>
                        <td><?php echo htmlspecialchars($grade['final_exam']); ?></td>
                        <td><?php echo htmlspecialchars($grade['total_mark']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">No students available</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <form method="POST">
        <input type="hidden" name="grade_level" value="<?php echo htmlspecialchars($gradeLevel); ?>">
        <button type="submit" class="approve-button" name="action" value="approve">Approve All Grades for Grade <?php echo htmlspecialchars($gradeLevel); ?></button>
    </form>
<?php endforeach; ?>

<?php if (empty($pending_grades)): ?>
    <tr><td colspan="8">No pending grades available.</td></tr>
<?php endif; ?>

    </div>
</body>
</html>
