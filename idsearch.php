<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lumamedb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize arrays to store student and teacher IDs by grade, stream, and department
$students = [
    'grade-9' => [],
    'grade-10' => [],
    'grade-11-natural' => [],
    'grade-11-social' => [],
    'grade-12-natural' => [],
    'grade-12-social' => []
];

$teachers = [];

// Mapping of database grade values to expected array keys
$gradeMapping = [
    '9' => 'grade-9',
    '10' => 'grade-10',
    '11' => 'grade-11-natural', // Default to natural
    '12' => 'grade-12-natural'  // Default to natural
];

// Query to retrieve students
$sql = "SELECT id, grade, stream FROM students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $grade = $row['grade'];
        $stream = strtolower($row['stream']); // Convert stream to lowercase

        // Determine the correct category for the student based on grade and stream
        if ($grade == '11' || $grade == '12') {
            if ($stream == 'natural') {
                $key = 'grade-'.$grade.'-natural';
            } elseif ($stream == 'social') {
                $key = 'grade-'.$grade.'-social';
            } else {
                echo "Unexpected stream value: $stream\n";
                continue;
            }
        } else {
            $key = $gradeMapping[$grade] ?? null;
        }

        // Categorize student IDs based on grade and stream
        if ($key && isset($students[$key])) {
            $students[$key][] = $id;
        } else {
            echo "Unexpected grade value: $grade\n";
        }
    }
} else {
    echo "No students found.\n";
}

// Query to retrieve teachers
$sql = "SELECT id, department FROM teachers";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $department = $row['department'];

        // Ensure the department array exists
        if (!isset($teachers[$department])) {
            $teachers[$department] = [];
        }

        // Categorize teacher IDs by department
        $teachers[$department][] = $id;
    }
} else {
    echo "No teachers found.\n";
}

$conn->close();

// Convert student and teacher arrays to comma-separated strings
foreach ($students as $grade => $ids) {
    $students[$grade] = implode(", ", $ids);
}

foreach ($teachers as $department => $ids) {
    $teachers[$department] = implode(", ", $ids);
}

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student and Teacher Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            margin-bottom: 10px;
        }
        .data-list {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="section">
        <h2>Student IDs grouped by Grade and Stream</h2>
        <ul class="data-list">
            <?php foreach ($students as $grade => $ids): ?>
                <li><strong>Grade:</strong> <?= htmlspecialchars($grade) ?> - <strong>Student IDs:</strong> <?= htmlspecialchars($ids) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="section">
        <h2>Teacher IDs grouped by Department</h2>
        <ul class="data-list">
            <?php foreach ($teachers as $department => $ids): ?>
                <li><strong>Department:</strong> <?= htmlspecialchars($department) ?> - <strong>Teacher IDs:</strong> <?= htmlspecialchars($ids) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
