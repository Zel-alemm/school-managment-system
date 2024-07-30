<?php
session_start();

// Initialize message variables
$message = "";
$message_type = "";

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
        die("Error in query: " . mysqli_error($conn));
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

// Fetch semesters
$semesters = [];
$semester_query = "SELECT semester_id, semester_name FROM semesters";
$semester_result = $conn->query($semester_query);

if ($semester_result) {
    while ($row = $semester_result->fetch_assoc()) {
        $semesters[] = $row;
    }
} else {
    die("Error fetching semesters: " . $conn->error);
}

// Handle teacher-student assignment
if (isset($_POST['assign_teacher_student'])) {
    $courseId = $_POST['course_id'];
    $studentIds = isset($_POST['student_ids']) ? explode(',', $_POST['student_ids']) : [];
    $teacherIds = isset($_POST['teacher_ids']) ? explode(',', $_POST['teacher_ids']) : [];
    $semesterId = $_POST['semester_id'];

    // Trim any extra spaces from student IDs and teacher IDs
    $studentIds = array_map('trim', $studentIds);
    $teacherIds = array_map('trim', $teacherIds);

    // Check if course exists
    $courseCheck = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
    $courseCheck->bind_param("s", $courseId);
    $courseCheck->execute();
    $courseCheck->store_result();

    if ($courseCheck->num_rows === 0) {
        $message = "Course ID does not exist.";
        $message_type = "error";
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
            $message = "Some Student IDs do not exist.";
            $message_type = "error";
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
            $message = "Some Teacher IDs do not exist.";
            $message_type = "error";
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
        // Prepare statement for checking existing assignments
        $checkExistStmt = $conn->prepare("SELECT student_id FROM student_teacher_assignments WHERE student_id = ? AND course_id = ?  AND semester_id = ?");
        if (!$checkExistStmt) {
          $message="";
        }

        // Prepare statement for insertion
        $stmt = $conn->prepare("INSERT INTO student_teacher_assignments (student_id, course_id, teacher_id, semester_id) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $duplicateAssignments = [];

        foreach ($studentIds as $studentId) {
            foreach ($teacherIds as $teacherId) {
                // Check if the assignment already exists
                $checkExistStmt->bind_param("sss", $studentId, $courseId, $semesterId);
                $checkExistStmt->execute();
                $checkExistStmt->store_result();

                if ($checkExistStmt->num_rows > 0) {
                    $duplicateAssignments[] = "Student ID: $studentId, Teacher ID: $teacherId, Course ID: $courseId, Semester ID: $semesterId";
                } else {
                    // Assign if not already assigned
                    $stmt->bind_param("ssss", $studentId, $courseId, $teacherId, $semesterId);
                    if (!$stmt->execute()) {
                        throw new Exception("Error assigning course $courseId to student $studentId and teacher $teacherId: " . $stmt->error);
                    }
                }
            }
        }
        $stmt->close();
        $checkExistStmt->close();

        if (!empty($duplicateAssignments)) {
             $message = "The following assignments already exist:\n"; //. implode("\n", $duplicateAssignments);
            $message_type = "error";
        } else {
            // Commit transaction
            $conn->commit();
            $message = "Assignments added successfully.";
            $message_type = "success";
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $message = "Failed to add assignments: " . $e->getMessage();
        $message_type = "error";
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
    <title>Admin Dashboard</title>
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
        input[type="text"], input[type="submit"], select {
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
            background-color: #007bff;
        }
        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .top-bar {
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            height: 60px;
            background-color: #393E46;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
        }
        .top-bar .welcome-message {
            color: #EEEEEE;
            font-size: 18px;
        }
    </style>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1>Assign Teachers & Students to Courses</h1>
       
        <form method="POST" action="">
        <?php if ($message != "") { ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo nl2br(htmlspecialchars($message)); ?>
            </div>
        <?php } ?>
            <label for="course_id">Course ID</label>
            <input type="text" id="course_id" name="course_id" required>

            <label for="student_ids">Student IDs (comma separated)</label>
            <input type="text" id="student_ids" name="student_ids">

            <label for="teacher_ids">Teacher IDs (comma separated)</label>
            <input type="text" id="teacher_ids" name="teacher_ids">

            <label for="semester_id">Semester</label>
            <select id="semester_id" name="semester_id" required>
                <?php foreach ($semesters as $semester) { ?>
                    <option value="<?php echo $semester['semester_id']; ?>"><?php echo htmlspecialchars($semester['semester_name']); ?></option>
                <?php } ?>
            </select>

            <input type="submit" name="assign_teacher_student" value="Assign">
        </form>
    </div>
</body>
</html>
