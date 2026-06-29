<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$msg = "";

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['subject_name']);
    $code = trim($_POST['subject_code']);
    $course_id = $_POST['course_id'];
    $semester_id = $_POST['semester_id'];

    if ($name && $code && $course_id && $semester_id) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code, course_id, semester_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $code, $course_id, $semester_id);
        $stmt->execute();
        $msg = "✅ Subject added.";
    }
}

// Delete subject
if (isset($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id = $del");
    header("Location: manage_subjects.php");
    exit;
}

$courses = $conn->query("SELECT id, name FROM courses");
$semesters = $conn->query("SELECT id, semester_number FROM semesters");
$subjects = $conn->query("
    SELECT s.id, s.subject_name, s.subject_code, c.name AS course_name, se.semester_number
    FROM subjects s
    JOIN courses c ON s.course_id = c.id
    JOIN semesters se ON s.semester_id = se.id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Manage Subjects</h2>
    <a href="dashboard.php">← Back to Dashboard</a><br><br>

    <?php if ($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>

    <form method="POST">
        <label>Subject Name:</label><br>
        <input type="text" name="subject_name" required><br>

        <label>Subject Code:</label><br>
        <input type="text" name="subject_code" required><br>

        <label>Course:</label><br>
<select name="course_id" id="course_id" required>
    <option value="">-- Select Course --</option>
    <?php while ($c = $courses->fetch_assoc()): ?>
        <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
    <?php endwhile; ?>
</select><br><br>

<label>Semester:</label><br>
<select name="semester_id" id="semester_id" required>
    <option value="">-- Select Course First --</option>
</select><br><br>


        <button type="submit">Add Subject</button>
    </form>

    <h3>Existing Subjects</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th><th>Name</th><th>Code</th><th>Course</th><th>Semester</th><th>Action</th>
        </tr>
        <?php while ($row = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['subject_name'] ?></td>
                <td><?= $row['subject_code'] ?></td>
                <td><?= $row['course_name'] ?></td>
                <td><?= $row['semester_number'] ?></td>
                <td><a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete subject?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <script>
document.getElementById('course_id').addEventListener('change', function () {
    const courseId = this.value;
    const semesterDropdown = document.getElementById('semester_id');

    // Clear previous options
    semesterDropdown.innerHTML = '<option value="">Loading...</option>';

    fetch(`fetch_semesters.php?course_id=${courseId}`)
        .then(res => res.json())
        .then(data => {
            semesterDropdown.innerHTML = '<option value="">-- Select Semester --</option>';
            data.forEach(sem => {
                const opt = document.createElement('option');
                opt.value = sem.id;
                opt.textContent = `Semester ${sem.semester_number} (${sem.semester_type})`;
                semesterDropdown.appendChild(opt);
            });
        })
        .catch(() => {
            semesterDropdown.innerHTML = '<option value="">Error loading semesters</option>';
        });
});
</script>

</body>
</html>
