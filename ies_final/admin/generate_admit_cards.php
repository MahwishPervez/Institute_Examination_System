<?php
include('../includes/db.php');
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch students eligible for admit card
$sql = "
SELECT 
    u.id as user_id, u.name, u.email,
    sea.id as application_id, sea.semester_id, s.semester_number, c.name AS course_name,
    a.attendance_percentage
FROM users u
JOIN student_exam_applications sea ON u.id = sea.user_id
JOIN semesters s ON sea.semester_id = s.id
JOIN courses c ON sea.course_id = c.id
JOIN attendance a ON u.id = a.user_id AND a.semester_id = s.id
WHERE u.role = 'student' AND a.attendance_percentage >= 75
";

$result = $conn->query($sql);
$eligible_students = $result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $semester_id = $_POST['semester_id'];

    // Create admit_card entry
    $stmt = $conn->prepare("INSERT INTO admit_cards (user_id, semester_id, issued_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $admit_card_id = $stmt->insert_id;

    // Get subject IDs from application
    $subject_sql = "
        SELECT ss.subject_id 
        FROM student_exam_applications sea
        JOIN student_subjects ss ON sea.id = ss.application_id
        WHERE sea.user_id = ? AND sea.semester_id = ?
    ";
    $stmt2 = $conn->prepare($subject_sql);
    $stmt2->bind_param("ii", $user_id, $semester_id);
    $stmt2->execute();
    $subjects = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Insert into admit_card_subjects
    $insert_stmt = $conn->prepare("INSERT INTO admit_card_subjects (admit_card_id, subject_id) VALUES (?, ?)");
    foreach ($subjects as $subject) {
        $insert_stmt->bind_param("ii", $admit_card_id, $subject['subject_id']);
        $insert_stmt->execute();
    }




    require_once '../vendor/tcpdf/tcpdf.php';

    // Fetch student details
    $details_sql = "
SELECT u.name, u.last_name, u.email, sp.father_name, sp.dob, sp.gender, sp.category,
       c.name AS course_name, c.course_type,
       s.semester_number, s.semester_type
FROM users u
JOIN student_profiles sp ON u.id = sp.user_id
JOIN student_exam_applications sea ON u.id = sea.user_id AND sea.semester_id = ?
JOIN courses c ON sea.course_id = c.id
JOIN semesters s ON sea.semester_id = s.id
WHERE u.id = ?
LIMIT 1
";
    $stmt3 = $conn->prepare($details_sql);
    $stmt3->bind_param("ii", $semester_id, $user_id);
    $stmt3->execute();
    $student = $stmt3->get_result()->fetch_assoc();

    // Fetch subjects
    $subjects_sql = "
SELECT sub.subject_name, sub.subject_code
FROM student_exam_applications sea
JOIN student_subjects ss ON sea.id = ss.application_id
JOIN subjects sub ON sub.id = ss.subject_id
WHERE sea.user_id = ? AND sea.semester_id = ?
";
    $stmt4 = $conn->prepare($subjects_sql);
    $stmt4->bind_param("ii", $user_id, $semester_id);
    $stmt4->execute();
    $subjects = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);

    // Generate PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Header
    $pdf->Cell(0, 10, 'ADMIT CARD', 0, 1, 'C');
    $pdf->Ln(5);

    // Student Info
    $html = "<strong>Name:</strong> {$student['name']} {$student['last_name']}<br>
<strong>Email:</strong> {$student['email']}<br>
<strong>Father's Name:</strong> {$student['father_name']}<br>
<strong>DOB:</strong> {$student['dob']}<br>
<strong>Gender:</strong> {$student['gender']}<br>
<strong>Category:</strong> {$student['category']}<br>
<strong>Course:</strong> {$student['course_name']} ({$student['course_type']})<br>
<strong>Semester:</strong> {$student['semester_number']} ({$student['semester_type']})<br><br>";

    $html .= "<strong>Subjects:</strong><ul>";
    foreach ($subjects as $sub) {
        $html .= "<li>{$sub['subject_code']} - {$sub['subject_name']}</li>";
    }
    $html .= "</ul>";

    $pdf->writeHTML($html);

    // Save PDF
    $filename = "admit_card_{$user_id}_sem{$semester_id}.pdf";
    $save_path = __DIR__ . "/../documents/admit_cards/" . $filename;
    $pdf->Output($save_path, 'F');

    // Update file_name in DB
    $update_stmt = $conn->prepare("UPDATE admit_cards SET file_name = ? WHERE id = ?");
    $update_stmt->bind_param("si", $filename, $admit_card_id);
    $update_stmt->execute();

    $success_message = "✅ Admit card generated successfully for student ID: $user_id";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Generate Admit Card</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h2>Generate Admit Card</h2>

    <?php if (!empty($success_message)) echo "<p style='color: green;'>$success_message</p>"; ?>

    <?php if (count($eligible_students) === 0): ?>
        <p>No eligible students found (Attendance must be ≥ 75%).</p>
    <?php else: ?>
        <form method="POST">
            <label for="user_id">Select Student:</label>
            <select name="user_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($eligible_students as $student): ?>
                    <option value="<?= $student['user_id'] ?>">
                        <?= $student['name'] ?> (<?= $student['course_name'] ?> - Sem <?= $student['semester_number'] ?>)
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label for="semester_id">Semester:</label>
            <select name="semester_id" required>
                <?php foreach ($eligible_students as $student): ?>
                    <option value="<?= $student['semester_id'] ?>">
                        <?= $student['semester_number'] ?> (<?= $student['course_name'] ?>)
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Generate Admit Card</button>
        </form>
    <?php endif; ?>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>

</html>