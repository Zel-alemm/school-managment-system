<?php
error_reporting(0);
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "lumamedb";

$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection error");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($data, $_POST['username']);
    $pass = $_POST['password'];

    // Function to check credentials
    function check_credentials($data, $table, $name, $pass) {
        $sql = "SELECT * FROM $table WHERE username = ?";
        $stmt = $data->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($pass == $row['password']) { // Using plain password check for this example
                return $row; // Return the user's data if credentials match
            }
        }
        return false; // Return false if no match is found
    }

    // Check each table for the user
    $user = check_credentials($data, 'admins', $name, $pass);
    if ($user === false) {
        $user = check_credentials($data, 'students', $name, $pass);
    }
    if ($user === false) {
        $user = check_credentials($data, 'teachers', $name, $pass);
    }

    if ($user !== false) {
        // Store the username and usertype in the session
        $_SESSION['username'] = $name;
        $_SESSION['usertype'] = $user['usertype'];

        // Redirect based on usertype
        if ($user['usertype'] == "admin") {
            header("Location: adminhome.php");
        } elseif ($user['usertype'] == "student") {
            header("Location: studenthome.php");
        } elseif ($user['usertype'] == "teacher") {
            header("Location: teacherhome.php");
        }
        exit();
    } else {
        $message = "Username or password does not match";
    }

    $_SESSION['loginMessage'] = $message;
    header("Location: login.php");
    exit();
}
?>
