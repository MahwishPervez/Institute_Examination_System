<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$msg = "";

// Add semester
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $semester_number = $_POST['semester_number'];
    $semester_type = $_POST['semester_type'];

    if ($course_id && $semester_number && $semester_type) {
        $stmt = $conn->prepare("INSERT INTO semesters (course_id, semester_number, semester_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $course_id, $semester_number, $semester_type);
        $stmt->execute();
        $msg = "✅ Semester added successfully.";
    }
}

// Delete semester
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM semesters WHERE id = $delete_id");
    header("Location: manage_semesters.php");
    exit;
}

// Fetch all courses and semesters
$courses = $conn->query("SELECT id, name FROM courses");
$semesters = $conn->query("
    SELECT s.id, s.semester_number, s.semester_type, c.name AS course_name
    FROM semesters s
    JOIN courses c ON s.course_id = c.id
    ORDER BY c.name, s.semester_number
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Semesters</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Manage Semesters</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <?php if ($msg): ?>
        <p style="color: green;"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Course:</label><br>
        <select name="course_id" required>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <option value="<?= $course['id'] ?>"><?= $course['name'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Semester Number:</label><br>
        <input type="number" name="semester_number" min="1" required><br><br>

        <label>Semester Type:</label><br>
        <select name="semester_type" required>
            <option value="odd">Odd</option>
            <option value="even">Even</option>
        </select><br><br>

        <button type="submit">Add Semester</button>
    </form>

    <h3>Existing Semesters</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>Course</th><th>Number</th><th>Type</th><th>Action</th>
        </tr>
        <?php while ($row = $semesters->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= $row['semester_number'] ?></td>
                <td><?= ucfirst($row['semester_type']) ?></td>
                <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this semester?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
