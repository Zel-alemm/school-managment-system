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

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the admin's full name
$adminUsername = $_SESSION['username'];
$stmt = $conn->prepare("SELECT fname, mname, lname FROM admins WHERE username = ?");
$stmt->bind_param("s", $adminUsername);
$stmt->execute();
$stmt->bind_result($fname, $mname, $lname);
$stmt->fetch();
$stmt->close();

// Combine names into full name
$fullName = trim($fname . ' ' . $mname . ' ' . $lname);

// Initialize message variable
$message = '';

// Handle course addition
if (isset($_POST['add_course'])) {
    $courseId = $_POST['course_id']; // Use input course_id
    $courseName = $_POST['course_name'];
    $courseReference = $_POST['course_reference'];

    // Check if the course ID already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE course_id = ?");
    $stmt->bind_param("s", $courseId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $message = "Course ID $courseId is already registered.";
    } else {
        // Insert the new course
        $stmt = $conn->prepare("INSERT INTO courses (course_id, course_name, reference) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $courseId, $courseName, $courseReference);

        if ($stmt->execute()) {
            $message = "Course added successfully. Inserted Course ID: $courseId";
        } else {
            $message = "Error adding course: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle course assignment
if (isset($_POST['assign_course'])) {
    $courseId = $_POST['course_id'];
    $studentIds = isset($_POST['student_ids']) ? explode(',', $_POST['student_ids']) : [];
    $teacherIds = isset($_POST['teacher_ids']) ? explode(',', $_POST['teacher_ids']) : [];

    // Trim any extra spaces from student IDs and teacher IDs
    $studentIds = array_map('trim', $studentIds);
    $teacherIds = array_map('trim', $teacherIds);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if the course exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE course_id = ?");
        $stmt->bind_param("s", $courseId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            throw new Exception("Course ID $courseId does not exist.");
        }

        // Assign course to students
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        foreach ($studentIds as $studentId) {
            // Check if student ID exists
            $checkStudent = $conn->prepare("SELECT COUNT(*) FROM students WHERE id = ?");
            $checkStudent->bind_param("s", $studentId);
            $checkStudent->execute();
            $checkStudent->bind_result($studentCount);
            $checkStudent->fetch();
            $checkStudent->close();

            if ($studentCount > 0) {
                // Check if the student is already assigned to the course
                $checkAssignment = $conn->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
                $checkAssignment->bind_param("ss", $studentId, $courseId);
                $checkAssignment->execute();
                $checkAssignment->bind_result($assignmentCount);
                $checkAssignment->fetch();
                $checkAssignment->close();

                if ($assignmentCount == 0) {
                    $stmt->bind_param("ss", $studentId, $courseId);
                    if (!$stmt->execute()) {
                        throw new Exception("Error assigning course to student $studentId: " . $stmt->error);
                    }
                } else {
                    echo "Student ID $studentId is already assigned to course $courseId.<br>";
                }
            } else {
                echo "Student ID $studentId does not exist.<br>";
            }
        }
        $stmt->close();

        // Assign course to teachers
        $stmt = $conn->prepare("INSERT INTO teacher_courses (teacher_id, course_id) VALUES (?, ?)");
        foreach ($teacherIds as $teacherId) {
            // Check if teacher ID exists
            $checkTeacher = $conn->prepare("SELECT COUNT(*) FROM teachers WHERE id = ?");
            $checkTeacher->bind_param("s", $teacherId);
            $checkTeacher->execute();
            $checkTeacher->bind_result($teacherCount);
            $checkTeacher->fetch();
            $checkTeacher->close();

            if ($teacherCount > 0) {
                // Check if the teacher is already assigned to the course
                $checkAssignment = $conn->prepare("SELECT COUNT(*) FROM teacher_courses WHERE teacher_id = ? AND course_id = ?");
                $checkAssignment->bind_param("ss", $teacherId, $courseId);
                $checkAssignment->execute();
                $checkAssignment->bind_result($assignmentCount);
                $checkAssignment->fetch();
                $checkAssignment->close();

                if ($assignmentCount == 0) {
                    $stmt->bind_param("ss", $teacherId, $courseId);
                    if (!$stmt->execute()) {
                        throw new Exception("Error assigning course to teacher $teacherId: " . $stmt->error);
                    }
                } else {
                    echo "Teacher ID $teacherId is already assigned to course $courseId.<br>";
                }
            } else {
                echo "Teacher ID $teacherId does not exist.<br>";
            }
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();
        $message = "Courses assigned successfully.";
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        $message = "Failed to assign courses: " . $e->getMessage();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        input[type="text"], input[type="number"], input[type="submit"] {
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
        .separator {
            margin: 20px 0;
            height: 1px;
            background-color: #00ADB5;
        }
    </style>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<div class="content">
    <h1>Add New Course</h1>
    <form method="POST" action="">
        <?php if (isset($message) && $message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <label for="course_id">Course ID:</label>
        <input type="text" id="course_id" name="course_id" required>
        <label for="course_name">Course Name:</label>
        <input type="text" id="course_name" name="course_name" required>
        <label for="course_reference">Course Reference:</label>
        <input type="text" id="course_reference" name="course_reference">
        <input type="submit" name="add_course" value="Add Course">
    </form>

    <div class="separator"></div>

    <h1>Assign Course</h1>
    <form method="POST" action="">
        <label for="course_id">Course ID:</label>
        <input type="text" id="course_id" name="course_id" required>
        <label for="student_ids">Student IDs (comma-separated):</label>
        <input type="text" id="student_ids" name="student_ids">
        <label for="teacher_ids">Teacher IDs (comma-separated):</label>
        <input type="text" id="teacher_ids" name="teacher_ids">
        <input type="submit" name="assign_course" value="Assign Course">
    </form>
</div>
</body>
</html>
