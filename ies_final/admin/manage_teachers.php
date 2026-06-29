<?php
include('../includes/db.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all teachers and all courses
$teachers = $conn->query("SELECT id, name FROM users WHERE role = 'teacher'");
$courses = $conn->query("SELECT id, name FROM courses");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $course_id = $_POST['course_id'];

    // Avoid duplicates
    $check = $conn->prepare("SELECT id FROM course_teachers WHERE teacher_id = ? AND course_id = ?");
    $check->bind_param("ii", $teacher_id, $course_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO course_teachers (teacher_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $teacher_id, $course_id);
        $stmt->execute();
        $msg = "✅ Course assigned successfully!";
    } else {
        $msg = "⚠️ Already assigned.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Assign Teachers to Courses</h2>

    <?php if ($msg): ?>
        <p style="color:green;"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Select Teacher:</label>
        <select name="teacher_id" required>
            <?php while ($t = $teachers->fetch_assoc()): ?>
                <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Select Course:</label>
        <select name="course_id" required>
            <?php while ($c = $courses->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Assign</button>
    </form>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
