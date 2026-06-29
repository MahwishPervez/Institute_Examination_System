<?php
session_start();
require './includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ./auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$dashboard_path = "./$role/dashboard.php"; // Dynamically resolve correct dashboard

// Fetch base user info
$stmt = $conn->prepare("SELECT name, last_name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// For students: fetch extra profile data
$student_profile = null;
if ($role === 'student') {
    $stmt2 = $conn->prepare("SELECT father_name, dob, gender, category FROM student_profiles WHERE user_id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $student_profile = $stmt2->get_result()->fetch_assoc();
}

// For teachers: show assigned courses
$assigned_courses = [];
if ($role === 'teacher') {
    $sql = "
        SELECT c.name FROM course_teachers ct
        JOIN courses c ON ct.course_id = c.id
        WHERE ct.teacher_id = ?
    ";
    $stmt3 = $conn->prepare($sql);
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    $assigned_courses = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst($role) ?> Profile</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
<a href="<?= $dashboard_path ?>"><button style="color: white; background-color: black;"> Back to Dashboard</button></a>
    <h2><?= ucfirst($role) ?> Profile</h2>

    <p><strong>Name:</strong> <?= $user['name'] . ' ' . $user['last_name'] ?></p>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
    <p><strong>Joined On:</strong> <?= $user['created_at'] ?></p>

    <?php if ($role === 'student' && $student_profile): ?>
        <h3>Student Details</h3>
        <p><strong>Father's Name:</strong> <?= $student_profile['father_name'] ?></p>
        <p><strong>Gender:</strong> <?= $student_profile['gender'] ?></p>
        <p><strong>Date of Birth:</strong> <?= $student_profile['dob'] ?></p>
        <p><strong>Category:</strong> <?= $student_profile['category'] ?></p>
    <?php endif; ?>

    <?php if ($role === 'teacher'): ?>
        <h3>Assigned Courses</h3>
        <?php if (count($assigned_courses) > 0): ?>
            <ul>
                <?php foreach ($assigned_courses as $course): ?>
                    <li><?= htmlspecialchars($course['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Not assigned to any course yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <br>
    
</body>
</html>
