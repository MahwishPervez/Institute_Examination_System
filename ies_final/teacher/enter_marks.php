<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php?role=teacher");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// Fetch course assigned to the teacher
$stmt = $conn->prepare("SELECT course_id FROM course_teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$course_result = $stmt->get_result();
$course = $course_result->fetch_assoc();

if (!$course) {
    die("You are not assigned to any course.");
}

$course_id = $course['course_id'];

// On form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'], $_POST['semester_id'])) {
    $subject_id = $_POST['subject_id'];
    $semester_id = $_POST['semester_id'];
    $marks_data = $_POST['marks'];

    $errors = [];

    foreach ($marks_data as $user_id => $data) {
        $internal = isset($data['internal']) ? (float) $data['internal'] : null;
        $internal_total = isset($data['internal_total']) ? (float) $data['internal_total'] : null;
        $external = isset($data['external']) ? (float) $data['external'] : null;
        $external_total = isset($data['external_total']) ? (float) $data['external_total'] : null;

        if ($internal === null || $internal_total === null || $external === null || $external_total === null) {
            $errors[] = "Missing fields for user ID $user_id.";
            continue;
        }

        if ($internal < 0 || $internal_total <= 0 || $external < 0 || $external_total <= 0) {
            $errors[] = "Negative or zero totals not allowed for user ID $user_id.";
            continue;
        }

        if ($internal > $internal_total) {
            $errors[] = "Internal marks scored cannot exceed total for user ID $user_id.";
            continue;
        }

        if ($external > $external_total) {
            $errors[] = "External marks scored cannot exceed total for user ID $user_id.";
            continue;
        }

        // Check if record exists
        $check = $conn->prepare("SELECT id FROM marks WHERE user_id = ? AND subject_id = ?");
        $check->bind_param("ii", $user_id, $subject_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE marks SET internal = ?, internal_total = ?, external = ?, external_total = ? WHERE user_id = ? AND subject_id = ?");
            $stmt->bind_param("ddddii", $internal, $internal_total, $external, $external_total, $user_id, $subject_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO marks (user_id, subject_id, internal, internal_total, external, external_total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidddd", $user_id, $subject_id, $internal, $internal_total, $external, $external_total);
        }
        $stmt->execute();
        if (empty($errors)) {
            $msg = "Marks submitted successfully.";
        } else {
            $msg = "Some errors occurred:<br>" . implode("<br>", $errors);
        }
    }

    
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Enter Marks</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            padding: 30px;
        }

        form {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label,
        select {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background-color: #1976d2;
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .msg {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
        <div>
            <a href="./dashboard.php">
                <button>Back</button>
            </a>
        </div>
    <form method="POST">
        <h2>Enter Student Marks</h2>

        <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

        <label>Select Semester:</label>
        <select name="semester_id" required onchange="this.form.submit()">
            <option value="">-- Select Semester --</option>
            <?php
            $stmt = $conn->prepare("SELECT id, semester_number, semester_type FROM semesters WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $semesters = $stmt->get_result();
            while ($sem = $semesters->fetch_assoc()) {
                $selected = ($_POST['semester_id'] ?? '') == $sem['id'] ? "selected" : "";
                echo "<option value='{$sem['id']}' $selected>Semester {$sem['semester_number']} ({$sem['semester_type']})</option>";
            }
            ?>
        </select>

        <?php if (!empty($_POST['semester_id'])): ?>
            <label>Select Subject:</label>
            <select name="subject_id" required onchange="this.form.submit()">
                <option value="">-- Select Subject --</option>
                <?php
                $semester_id = $_POST['semester_id'];
                $stmt = $conn->prepare("SELECT id, subject_name, subject_code FROM subjects WHERE course_id = ? AND semester_id = ?");
                $stmt->bind_param("ii", $course_id, $semester_id);
                $stmt->execute();
                $subjects = $stmt->get_result();
                while ($subject = $subjects->fetch_assoc()) {
                    $selected = ($_POST['subject_id'] ?? '') == $subject['id'] ? "selected" : "";
                    echo "<option value='{$subject['id']}' $selected>{$subject['subject_code']} - {$subject['subject_name']}</option>";
                }
                ?>
            </select>
        <?php endif; ?>

        <?php
        if (!empty($_POST['semester_id']) && !empty($_POST['subject_id'])):
            $semester_id = $_POST['semester_id'];
            $subject_id = $_POST['subject_id'];

            $stmt = $conn->prepare("
        SELECT u.id, u.name, u.last_name
        FROM student_exam_applications sea
        JOIN student_subjects ss ON sea.id = ss.application_id
        JOIN users u ON sea.user_id = u.id
        WHERE sea.course_id = ? AND sea.semester_id = ? AND ss.subject_id = ?
      ");
            $stmt->bind_param("iii", $course_id, $semester_id, $subject_id);
            $stmt->execute();
            $students = $stmt->get_result();
        ?>

            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Internal Marks</th>
                    <th>Internal Total</th>
                    <th>External Marks</th>
                    <th>External Total</th>
                </tr>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $student['name'] . ' ' . $student['last_name'] ?></td>
                        <td><input type="number" name="marks[<?= $student['id'] ?>][internal]" step="0.01" min="0" required></td>
                        <td><input type="number" name="marks[<?= $student['id'] ?>][internal_total]" step="0.01" min="0" required></td>
                        <td><input type="number" name="marks[<?= $student['id'] ?>][external]" step="0.01" min="0" required></td>
                        <td><input type="number" name="marks[<?= $student['id'] ?>][external_total]" step="0.01" min="0" required></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <button type="submit">Submit Marks</button>
        <?php endif; ?>
    </form>

</body>

</html>