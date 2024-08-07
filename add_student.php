<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] != 'admin') {
    header("location:login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "lumamedb";

$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection error: " . mysqli_connect_error());
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

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $grade = $_POST['grade'];
    $stream = $_POST['stream'];
    $usertype = 'student'; // User type is always 'student'
    $password = $lname . '@1234'; // Auto-generated password

    // Check if student with same details already exists
    $check_user_sql = "SELECT id FROM students WHERE fname = ? AND mname = ? AND lname = ? AND phone = ?";
    $stmt = $data->prepare($check_user_sql);

    if ($stmt) {
        $stmt->bind_param("sssi", $fname, $mname, $lname, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Student with these details already exists.";
        } else {
            // Generate a unique ID based on grade
            $newID = generateID($data, $grade);

            // Insert into user table
            $insert_sql = "INSERT INTO students (id, fname, mname, lname, age, email, phone, usertype, password, username, grade, stream)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $data->prepare($insert_sql);

            if ($insert_stmt) {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $insert_stmt->bind_param("ssssisisssss", $newID, $fname, $mname, $lname, $age, $email, $phone, $usertype, $password_hash, $newID, $grade, $stream);

                if ($insert_stmt->execute()) {
                    $message = "Student added successfully.";
                } else {
                    $message = "Error inserting student: " . $insert_stmt->error;
                }
                $insert_stmt->close();
            } else {
                $message = "Error preparing insert statement: " . $data->error;
            }
        }
        $stmt->close();
    } else {
        $message = "Error preparing check statement: " . $data->error;
    }
}

$data->close();

// Function to generate the next ID based on grade
function generateID($conn, $grade) {
    $prefixes = [
        '9' => 'LSSS170',
        '10' => 'LSSS160',
        '11' => 'LSSS150',
        '12' => 'LSSS140',
    ];

    $prefix = $prefixes[$grade];
    $startNumber = 100;
    $numLength = 3;

    $sql = "SELECT id FROM students WHERE id LIKE '$prefix%' ORDER BY id DESC LIMIT 1";
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
    <title>Add Student</title>
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
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border: 1px solid #393E46;
            border-radius: 5px;
            box-sizing: border-box;
            background: #222831;
            color: #EEEEEE;
        }
        button {
            background-color: #00ADB5;
            color: #EEEEEE;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background-color: #007A7A;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            color: #00ADB5;
        }
    </style>
</head>
<body>
<header class="header">
    <a href="#">Admin Dashboard</a>
    <div class="username">Welcome, <?php echo $fullName; ?></div>
    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</header>
<?php include 'admin_sidebar.php'; ?>

<div class="content">
    <h1>Add New Student</h1>
    <form method="POST">
        <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <label for="fname">First Name:</label>
        <input type="text" id="fname" name="fname" required>
        <label for="mname">Middle Name:</label>
        <input type="text" id="mname" name="mname" required>
        <label for="lname">Last Name:</label>
        <input type="text" id="lname" name="lname" required>
        <label for="age">Age:</label>
        <input type="number" id="age" name="age" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>
        <label for="grade">Grade:</label>
        <select id="grade" name="grade" required>
            <option value="9">Grade 9</option>
            <option value="10">Grade 10</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
        </select>
        <label for="stream">Stream:</label>
        <select id="stream" name="stream" required>
            <option value="Non-Stream">Non-Stream</option>
            <option value="Natural">Natural Science</option>
            <option value="Social">Social Science</option>
        </select>
        <input type="hidden" id="usertype" name="usertype" value="student">
        <button type="submit">Add Student</button>
    </form>
</div>
</body>
</html>
