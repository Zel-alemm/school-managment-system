<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "lumamedb";

$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection error");
}

// Fetch the admin's full name
$username = mysqli_real_escape_string($data, $_SESSION['username']);
$sql_user = "SELECT fname, mname, lname FROM admins WHERE username = ?";
$stmt_user = $data->prepare($sql_user);

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

// Fetch courses data along with semester information
$sql = "SELECT c.course_id, c.course_name, c.reference, e.semester_id 
        FROM courses c
        JOIN enrollments e ON c.course_id = e.course_id
        ORDER BY e.semester_id";
$result = mysqli_query($data, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard</title><!-- Link to your external CSS -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #393E46;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #00ADB5;
        }
        th {
            background-color: #00ADB5;
            color: #222831;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1 class="text-center">Available Courses</h1>
        <?php
        if (mysqli_num_rows($result) > 0) {
            $current_semester = -1;
            while ($row = mysqli_fetch_assoc($result)) {
                if ($current_semester != $row['semester_id']) {
                    if ($current_semester != -1) {
                        echo "</tbody></table>";
                    }
                    $current_semester = $row['semester_id'];
                    echo "<h2>Semester " . htmlspecialchars($current_semester) . "</h2>";
                    echo "<table class='table table-striped'>";
                    echo "<thead><tr><th>Course ID</th><th>Course Name</th><th>Reference</th></tr></thead><tbody>";
                }
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['reference']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No courses available</p>";
        }
        ?>
    </div>
</body>
</html>
