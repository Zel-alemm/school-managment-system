<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['usertype'] != 'admin') {
    header("location:login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "lumamedb"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        die("Error in query: " . mysqli_error($data));
    }

    $user_info = $result_user->fetch_assoc();

    if ($user_info) {
        $fullName = htmlspecialchars($user_info['fname']) . ' ' . htmlspecialchars($user_info['mname']) . ' ' . htmlspecialchars($user_info['lname']);
    } else {
        $fullName = "Unknown User";
    }

    $stmt_user->close();
} else {
    die("Error preparing query: " . $data->error);
}

// Handle teacher-student assignment
if (isset($_POST['assign_teacher_student'])) {
    $courseId = $_POST['course_id'];
    $studentIds = isset($_POST['student_ids']) ? explode(',', $_POST['student_ids']) : [];
    $teacherIds = isset($_POST['teacher_ids']) ? explode(',', $_POST['teacher_ids']) : [];

    // Trim any extra spaces from student IDs and teacher IDs
    $studentIds = array_map('trim', $studentIds);
    $teacherIds = array_map('trim', $teacherIds);

    // Check if course exists
    $courseCheck = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
    $courseCheck->bind_param("s", $courseId);
    $courseCheck->execute();
    $courseCheck->store_result();

    if ($courseCheck->num_rows === 0) {
        echo "Course ID does not exist.";
        $courseCheck->close();
        $conn->close();
        exit();
    }
    $courseCheck->close();

    // Check if student and teacher IDs exist
    $studentIdsValid = $teacherIdsValid = true;

    // Check students
    if (!empty($studentIds)) {
        $studentsCheck = $conn->prepare("SELECT id FROM students WHERE id IN (" . implode(',', array_fill(0, count($studentIds), '?')) . ")");
        $studentsCheck->bind_param(str_repeat('s', count($studentIds)), ...$studentIds);
        $studentsCheck->execute();
        $studentsCheck->store_result();
        if ($studentsCheck->num_rows < count($studentIds)) {
            $studentIdsValid = false;
            echo "Some Student IDs do not exist.";
        }
        $studentsCheck->close();
    }

    // Check teachers
    if (!empty($teacherIds)) {
        $teachersCheck = $conn->prepare("SELECT id FROM teachers WHERE id IN (" . implode(',', array_fill(0, count($teacherIds), '?')) . ")");
        $teachersCheck->bind_param(str_repeat('s', count($teacherIds)), ...$teacherIds);
        $teachersCheck->execute();
        $teachersCheck->store_result();
        if ($teachersCheck->num_rows < count($teacherIds)) {
            $teacherIdsValid = false;
            echo "Some Teacher IDs do not exist.";
        }
        $teachersCheck->close();
    }

    if (!$studentIdsValid || !$teacherIdsValid) {
        $conn->close();
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare statement for insertion
        $stmt = $conn->prepare("INSERT INTO student_teacher_assignments (student_id, course_id, teacher_id) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        foreach ($studentIds as $studentId) {
            foreach ($teacherIds as $teacherId) {
                $stmt->bind_param("sss", $studentId, $courseId, $teacherId);
                if (!$stmt->execute()) {
                    throw new Exception("Error assigning course $courseId to student $studentId and teacher $teacherId: " . $stmt->error);
                }
            }
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        echo "Assignments added successfully.";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        echo "Failed to add assignments: " . $e->getMessage();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard</title><!-- Link to your external CSS -->
    <style>
         <style>
        .content {
            margin-left: 220px;
            padding: 20px;
            margin-top: 80px;
            border-radius: 8px;
        }
        h1 {
            color: #00ADB5;
            text-align: center;
        }
        form {
            background-color: #393E46;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            max-width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #00ADB5;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border: 1px solid #393E46;
            border-radius: 5px;
            box-sizing: border-box;
            background: #222831;
            color: #EEEEEE;
        }
        button, input[type="submit"] {
            background-color: #00ADB5;
            color: #EEEEEE;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #007A7A;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            color: #00ADB5;
        }
    </style>
    </style>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1>Assign Teachers and Students to Courses</h1>
        <form method="POST" action="">
            <label for="course_id">Course ID:</label>
            <input type="text" id="course_id" name="course_id" required>
            <br><br>
            <label for="student_ids">Student IDs (comma-separated):</label>
            <input type="text" id="student_ids" name="student_ids">
            <br><br>
            <label for="teacher_ids">Teacher IDs (comma-separated):</label>
            <input type="text" id="teacher_ids" name="teacher_ids">
            <br><br>
            <input type="submit" name="assign_teacher_student" value="Assign">
        </form>
    </div>
</body>
</html>
