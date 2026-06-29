<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $delete_id AND role = 'student'");
    header("Location: manage_students.php");
    exit;
}

// Get all students with course & semester
$sql = "
SELECT u.id, u.name, u.email, sp.father_name, sp.gender, sp.dob, c.name AS course_name, s.semester_number
FROM users u
JOIN student_profiles sp ON u.id = sp.user_id
JOIN student_exam_applications sea ON u.id = sea.user_id
JOIN courses c ON sea.course_id = c.id
JOIN semesters s ON sea.semester_id = s.id
WHERE u.role = 'student'
ORDER BY u.name
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Manage Students</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Father</th><th>DOB</th><th>Gender</th><th>Course</th><th>Semester</th><th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['father_name'] ?></td>
                <td><?= $row['dob'] ?></td>
                <td><?= $row['gender'] ?></td>
                <td><?= $row['course_name'] ?></td>
                <td><?= $row['semester_number'] ?></td>
                <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete student?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
