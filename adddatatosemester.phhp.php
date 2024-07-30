<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "lumamedb"; // Your database name

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to insert data
$sql = "
INSERT INTO semesters (semester_id, semester_name) VALUES
(1, '1'),
(2, '2')
";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully into 'semesters' table.";
} else {
    echo "Error inserting data: " . $conn->error;
}

// Close the connection
$conn->close();
?>
