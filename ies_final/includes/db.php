<?php
// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'redesigned';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch students who have submitted the application form
function getStudentsWithApplication() {
    global $conn;

    $sql = "
        SELECT 
            u.id,
            u.name,
            u.email,
            c.name AS course,
            s.semester_number,
            s.semester_type
        FROM users u
        JOIN student_profiles sp ON u.id = sp.user_id
        JOIN student_exam_applications sea ON sea.user_id = u.id
        JOIN courses c ON sea.course_id = c.id
        JOIN semesters s ON sea.semester_id = s.id
        WHERE u.role = 'student'
        ORDER BY u.name ASC
    ";

    $result = $conn->query($sql);
    $students = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }

    return $students;
}
?>
