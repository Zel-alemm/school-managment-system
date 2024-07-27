<?php
// Database connection details
$host = "localhost";
$user = "root";
$password = "";
$dbname = "lumamedb";

// Create connection
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// SQL to create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS admins (
        id VARCHAR(10) PRIMARY KEY,
        fname VARCHAR(50),
        mname VARCHAR(50),
        lname VARCHAR(50),
        age INT,
        email VARCHAR(100),
        phone VARCHAR(15),
        usertype VARCHAR(10),
        password VARCHAR(255),
        username VARCHAR(50)
    )",
    
    "CREATE TABLE IF NOT EXISTS students (
        id VARCHAR(10) PRIMARY KEY,
        fname VARCHAR(50),
        mname VARCHAR(50),
        lname VARCHAR(50),
        age INT,
        email VARCHAR(100),
        phone VARCHAR(15),
        usertype VARCHAR(10),
        password VARCHAR(255),
        username VARCHAR(50),
        grade VARCHAR(10)
    )",
    
    "CREATE TABLE IF NOT EXISTS teachers (
        id VARCHAR(10) PRIMARY KEY,
        fname VARCHAR(50),
        mname VARCHAR(50),
        lname VARCHAR(50),
        email VARCHAR(100),
        phone VARCHAR(15),
        department VARCHAR(100),
        usertype VARCHAR(10),
        password VARCHAR(255),
        username VARCHAR(50)
    )",
    
    "CREATE TABLE IF NOT EXISTS courses (
        course_id VARCHAR(10) PRIMARY KEY,
        course_name VARCHAR(100),
        reference VARCHAR(255)
    )",
    
    "CREATE TABLE IF NOT EXISTS enrollments (
        student_id VARCHAR(10),
        course_id VARCHAR(10),
        PRIMARY KEY (student_id, course_id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (course_id) REFERENCES courses(course_id)
    )",
    
    "CREATE TABLE IF NOT EXISTS teacher_courses (
        teacher_id VARCHAR(10),
        course_id VARCHAR(10),
        PRIMARY KEY (teacher_id, course_id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id),
        FOREIGN KEY (course_id) REFERENCES courses(course_id)
    )",

    "CREATE TABLE IF NOT EXISTS pending_grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(10),
        course_id VARCHAR(10),
        teacher_id VARCHAR(10),
        quiz FLOAT,
        midterm FLOAT,
        assignment FLOAT,
        final_exam FLOAT,
        total_mark FLOAT GENERATED ALWAYS AS (quiz + midterm + assignment + final_exam) STORED,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (course_id) REFERENCES courses(course_id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    )",

    "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(10),
        course_id VARCHAR(10),
        teacher_id VARCHAR(10),
        quiz FLOAT,
        midterm FLOAT,
        assignment FLOAT,
        final_exam FLOAT,
        total_mark FLOAT GENERATED ALWAYS AS (quiz + midterm + assignment + final_exam) STORED,
        grade CHAR(2) GENERATED ALWAYS AS (
            CASE
                WHEN total_mark >= 90 THEN 'A'
                WHEN total_mark >= 80 THEN 'B'
                WHEN total_mark >= 70 THEN 'C'
                WHEN total_mark >= 60 THEN 'D'
                ELSE 'F'
            END
        ) STORED,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (course_id) REFERENCES courses(course_id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    )"
];

// Execute the SQL queries
foreach ($tables as $table) {
    if ($conn->query($table) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();
?>
