<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'student') {
    header("location:login.php");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$password = "";
$db = "lumamedb";

$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection error: " . mysqli_connect_error());
}

$message = ""; // Variable to store messages

if (isset($_POST['accept'])) {
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if (empty($email) || empty($phone)) {
        $message = "Email and phone are required.";
    } else {
        // Fetch the applicant details
        $sql = "SELECT fname, mname, lname, age, email, phone, grade FROM admission WHERE email = ? AND phone = ?";
        if ($stmt = $data->prepare($sql)) {
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $message = "No application found with the given email and phone.";
            } else {
                $applicant = $result->fetch_assoc();

                // Check if the applicant is already registered
                $check_user_sql = "SELECT id FROM user WHERE email = ? OR phone = ?";
                if ($check_stmt = $data->prepare($check_user_sql)) {
                    $check_stmt->bind_param("ss", $email, $phone);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        $message = "Attempt to register multiple times. This applicant is already registered.";
                    } else {
                        // Generate a unique ID and password for the new user
                        $usertype = 'student'; // Adjust as needed
                        $newID = generateID($data, $usertype);
                        $password = $applicant['lname'] . '@1234';

                        // Insert into user table
                        $insert_sql = "INSERT INTO user (id, fname, mname, lname, age, email, phone, usertype, password, username, grade)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        if ($insert_stmt = $data->prepare($insert_sql)) {
                            $insert_stmt->bind_param("ssssissssss", $newID, $applicant['fname'], $applicant['mname'], $applicant['lname'], $applicant['age'], $applicant['email'], $applicant['phone'], $usertype, password_hash($password, PASSWORD_BCRYPT), $newID, $applicant['grade']);

                            if ($insert_stmt->execute()) {
                                // Remove from admission table
                                $delete_sql = "DELETE FROM admission WHERE email = ? AND phone = ?";
                                if ($delete_stmt = $data->prepare($delete_sql)) {
                                    $delete_stmt->bind_param("ss", $email, $phone);
                                    $delete_stmt->execute();

                                    if ($delete_stmt->affected_rows > 0) {
                                        $message = "Application accepted and registered successfully.";
                                    } else {
                                        $message = "Failed to remove application from admission table.";
                                    }
                                    $delete_stmt->close();
                                } else {
                                    $message = "Error preparing delete statement: " . $data->error;
                                }
                            } else {
                                $message = "Error inserting user: " . $insert_stmt->error;
                            }
                            $insert_stmt->close();
                        } else {
                            $message = "Error preparing insert statement: " . $data->error;
                        }
                    }
                    $check_stmt->close();
                } else {
                    $message = "Error preparing check statement: " . $data->error;
                }
            }
            $stmt->close();
        } else {
            $message = "Error preparing select statement: " . $data->error;
        }
    }
}

$username = mysqli_real_escape_string($data, $_SESSION['username']);
$sql_user = "SELECT fname, mname, lname FROM user WHERE username = '$username'";
$result_user = mysqli_query($data, $sql_user);

if ($result_user === false) {
    die("Error in query: " . mysqli_error($data));
}

$user_info = mysqli_fetch_assoc($result_user);

if ($user_info) {
    $fullName = htmlspecialchars($user_info['fname']) . ' ' . htmlspecialchars($user_info['mname']) . ' ' . htmlspecialchars($user_info['lname']);
} else {
    $fullName = "Unknown User";
}

// Fetch the admission data
$sql = "SELECT fname, mname, lname, age, email, phone, grade, message FROM admission WHERE verified = FALSE";
$result = mysqli_query($data, $sql);

if ($result === false) {
    die("Error in query: " . mysqli_error($data));
}

$data->close();

function generateID($conn, $usertype) {
    if ($usertype == 'admin') {
        $prefix = 'LSST170';
        $startNumber = 100;
        $numLength = 3;
    } else {
        $prefix = 'LSS170';
        $startNumber = 1000;
        $numLength = 4;
    }

    $sql = "SELECT id FROM user WHERE id LIKE '$prefix%' ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $lastID = $result->fetch_assoc()['id'];
        $number = (int)substr($lastID, -$numLength);
        $newNumber = str_pad($number + 1, $numLength, '0', STR_PAD_LEFT);
    } else {
        $newNumber = str_pad($startNumber, $numLength, '0', STR_PAD_LEFT);
    }

    return $prefix . $newNumber;
}
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
            border-collapse: collapse;
            width: 80%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .message {
            color: green;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="#">Admin Dashboard</a>
        <div class="username">Welcome, <?php echo htmlspecialchars($fullName); ?></div>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <?php include 'admin_sidebar.php'; ?>

    <div class="content">
        <h1 style="text-align:center">Applied For Admission</h1>
        <br><br>
        <div class="message">
            <?php if (!empty($message)) echo htmlspecialchars($message); ?>
        </div>
        <br>
        <center>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Grade</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
                <?php
                while ($info = $result->fetch_assoc()) {
                    $fullname = htmlspecialchars($info['fname']) . ' ' . htmlspecialchars($info['mname']) . ' ' . htmlspecialchars($info['lname']);
                    ?>
                    <tr>
                        <td><?php echo $fullname; ?></td>
                        <td><?php echo htmlspecialchars($info['age']); ?></td>
                        <td><?php echo htmlspecialchars($info['email']); ?></td>
                        <td><?php echo htmlspecialchars($info['phone']); ?></td>
                        <td><?php echo htmlspecialchars($info['grade']); ?></td>
                        <td><?php echo htmlspecialchars($info['message']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($info['email']); ?>">
                                <input type="hidden" name="phone" value="<?php echo htmlspecialchars($info['phone']); ?>">
                                <button type="submit" name="accept" class="btn btn-success">Accept</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </center>
    </div>
</body>
</html>
