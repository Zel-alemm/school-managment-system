<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
} elseif ($_SESSION['usertype'] == 'student') {
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

if (isset($_POST['accept'])) {
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Fetch the applicant details
    $sql = "SELECT fname, mname, lname, age, email, phone, grade FROM admission WHERE email = ? AND phone = ?";
    if ($stmt = $data->prepare($sql)) {
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            die("No application found with the given email and phone.");
        }

        $applicant = $result->fetch_assoc();

        // Check if the applicant is already registered
        $check_user_sql = "SELECT id FROM user WHERE email = ? OR phone = ?";
        if ($check_stmt = $data->prepare($check_user_sql)) {
            $check_stmt->bind_param("ss", $email, $phone);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                echo "Attempt to register multiple times. This applicant is already registered.";
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
                                echo "Application accepted and registered successfully.";
                            } else {
                                echo "Failed to remove application from admission table.";
                            }
                            $delete_stmt->close();
                        } else {
                            echo "Error preparing delete statement: " . $data->error;
                        }
                    } else {
                        echo "Error inserting user: " . $insert_stmt->error;
                    }
                    $insert_stmt->close();
                } else {
                    echo "Error preparing insert statement: " . $data->error;
                }
            }
            $check_stmt->close();
        } else {
            echo "Error preparing check statement: " . $data->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing select statement: " . $data->error;
    }
}

$data->close();

// Function to generate the next ID (you may want to adjust this as needed)
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
