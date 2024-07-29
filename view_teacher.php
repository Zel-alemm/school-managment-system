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

// Fetch teachers
$sql = "SELECT id, username, fname, mname, lname, email, phone, department FROM teachers ORDER BY department";
$result = $conn->query($sql);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

// Organize teachers by department
$teachersByDepartment = [];
while ($row = $result->fetch_assoc()) {
    $department = $row['department'];
    if (!isset($teachersByDepartment[$department])) {
        $teachersByDepartment[$department] = [];
    }
    $teachersByDepartment[$department][] = $row;
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
        <h1>Teacher Information by Department</h1>

        <?php foreach ($teachersByDepartment as $department => $teachers): ?>
            <h2>Department: <?php echo htmlspecialchars($department); ?></h2>
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
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['fname']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['mname']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['lname']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No teachers available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br>
        <?php endforeach; ?>
    </div>
</body>
</html>
