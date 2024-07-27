<?php
session_start(); // Ensure this is at the very beginning of the file

$host = "localhost";
$user = "root";
$password = "";
$db = "lumamedb";

// Create a connection to the database
$data = new mysqli($host, $user, $password, $db);

// Check the connection
if ($data->connect_error) {
    die("Connection error: " . $data->connect_error);
}

$message = ""; // Initialize $message variable

if (isset($_POST['apply'])) {
    // Capture form data
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $grade = $_POST['grade'];
    $message = $_POST['message'];

    // Debugging output
    // echo "fname: $fname, mname: $mname, lname: $lname, age: $age, email: $email, phone: $phone, grade: $grade, message: $message<br>";

    // Check if the entry already exists
    $check_stmt = $data->prepare("SELECT COUNT(*) FROM admission WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        $message = "You are attempting to register multiple times. An entry with the same email already exists in the admission table.";
    } else {
        // Prepare the SQL statement
        $stmt = $data->prepare("INSERT INTO admission (fname, mname, lname, age, email, phone, grade, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === FALSE) {
            die("Prepare failed: " . $data->error);
        }

        // Bind parameters
        $stmt->bind_param("sssisiss", $fname, $mname, $lname, $age, $email, $phone, $grade, $message);

        // Execute the statement
        if ($stmt->execute()) {
           $message = "Your application was sent successfully.";
        } else {
            $message = "Apply failed: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}

// Store message in session
$_SESSION['registermessage'] = $message;

// Redirect with header
header("Location: login.php#registerForm");
exit();

// Close the connection
$data->close();
?>
