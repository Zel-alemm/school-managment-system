php
Copy code
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
        $sql = "SELECT fname, mname, lname, age, email, phone, grade, stream FROM admission WHERE email = ? AND phone = ?";
        if ($stmt = $data->prepare($sql)) {
            $stmt->bind_param("ss", $email, $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $message = "No application found with the given email and phone.";
            } else {
                $applicant = $result->fetch_assoc();

                // Check if the applicant is already registered
                $check_user_sql = "SELECT id FROM students WHERE fname = ? AND mname = ? AND lname = ? AND phone = ?";
                if ($check_stmt = $data->prepare($check_user_sql)) {
                    $check_stmt->bind_param("ssss", $applicant['fname'], $applicant['mname'], $applicant['lname'], $phone);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        $message = "Attempt to register multiple times. This applicant is already registered.";
                    } else {
                        // Generate a unique ID and password for the new user
                        $usertype = 'student'; // Adjust as needed
                        $grade = $applicant['grade']; // Extract the grade
                        $newID = generateID($data, $usertype, $grade); // Pass the grade as the third argument
                        $password = strtolower($applicant['lname']) . '@1234';

                        // Insert into user table
                        $insert_sql = "INSERT INTO students (id, fname, mname, lname, age, email, phone, usertype, password, username, grade, stream)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        if ($insert_stmt = $data->prepare($insert_sql)) {
                            $insert_stmt->bind_param("ssssisisssss", $newID, $applicant['fname'], $applicant['mname'], $applicant['lname'], $applicant['age'], $applicant['email'], $applicant['phone'], $usertype, password_hash($password, PASSWORD_BCRYPT), $newID, $applicant['grade'], $applicant['stream']);

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
$sql_user = "SELECT fname, mname, lname FROM admins WHERE username = '$username'";
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
$sql = "SELECT fname, mname, lname, age, email, phone, grade, message, stream FROM admission WHERE verified = FALSE";
$result = mysqli_query($data, $sql);

if ($result === false) {
    die("Error in query: " . mysqli_error($data));
}

$data->close();

function generateID($conn, $usertype, $grade) {
    // Determine the prefix based on the grade
    switch ($grade) {
        case 9:
            $prefix = 'LSSS170';
            break;
        case 10:
            $prefix = 'LSSS160';
            break;
        case 11:
            $prefix = 'LSSS150';
            break;
        case 12:
            $prefix = 'LSSS140';
            break;
        default:
            $prefix = 'LSSS999'; // Default prefix for unknown grade
            break;
    }

    // SQL query to fetch the last ID with the specific prefix
    $sql = "SELECT id FROM students WHERE id LIKE ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likeParam = $prefix . '%';
    $stmt->bind_param("s", $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die("Error in query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastID = $row['id'];
        // Extract the numeric part from the ID
        $number = (int)substr($lastID, strlen($prefix));
        $newNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '100'; // Starting number if no previous records
    }

    $newID = $prefix . $newNumber;

    return $newID;
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
                    <th>Stream</th>
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
                        <td><?php echo htmlspecialchars($info['stream']); ?></td>
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
