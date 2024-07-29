<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] != 'admin') {
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

$adminUsername = $_SESSION['username'];
$sqlAdmin = "SELECT fname, mname, lname FROM admins WHERE username = ?";
$stmtAdmin = $conn->prepare($sqlAdmin);

if ($stmtAdmin === false) {
    die("Prepare failed: " . $conn->error);
}

$stmtAdmin->bind_param("s", $adminUsername);
$stmtAdmin->execute();
$stmtAdmin->bind_result($adminFname, $adminMname, $adminLname);
$stmtAdmin->fetch();
$stmtAdmin->close();

// Combine admin names
$fullName = trim($adminFname . ' ' . $adminMname . ' ' . $adminLname);

// Sanitize output
$fullName = htmlspecialchars($fullName);
// Fetch students grouped by grade
$sql = "SELECT grade, id, username, fname, mname, lname, email, phone, grade FROM students ORDER BY grade";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

// Organize students by grade
$studentsByGrade = [];
while ($row = $result->fetch_assoc()) {
    $grade = $row['grade'];
    if (!isset($studentsByGrade[$grade])) {
        $studentsByGrade[$grade] = [];
    }
    $studentsByGrade[$grade][] = $row;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #00ADB5;
            color: #EEEEEE;
        }

        tr:nth-child(even) {
            background-color: #222831;
        }

        tr:hover {
            background-color: #222831;
        }

        h2 {
            color: #00ADB5;
        }
    </style>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <h1>Student Information by Grade</h1>

    <?php foreach ($studentsByGrade as $grade => $students): ?>
        <h2>Grade <?php echo htmlspecialchars($grade); ?></h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['fname']); ?></td>
                            <td><?php echo htmlspecialchars($student['mname']); ?></td>
                            <td><?php echo htmlspecialchars($student['lname']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">No students available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
    <?php endforeach; ?>
</div>
</body>
</html>

