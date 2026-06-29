<?php
include('../includes/db.php');
require_once '../vendor/tcpdf/tcpdf.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $semester_id = $_POST['semester_id'];

    // Fetch student details
    $sql = "SELECT u.name, u.last_name, u.email, sp.father_name, sp.dob, s.semester_number, c.name AS course
            FROM users u
            JOIN student_profiles sp ON u.id = sp.user_id
            JOIN student_exam_applications sea ON sea.user_id = u.id
            JOIN semesters s ON sea.semester_id = s.id
            JOIN courses c ON sea.course_id = c.id
            WHERE u.id = ? AND s.id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        die("Student or semester not found.");
    }

    // Fetch marks
    $sql = "SELECT sub.subject_name, m.internal, m.internal_total, m.external, m.external_total
            FROM marks m
            JOIN subjects sub ON m.subject_id = sub.id
            WHERE m.user_id = ? AND sub.semester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $marks_result = $stmt->get_result();

    $marks = [];
    $passed = true;

    while ($row = $marks_result->fetch_assoc()) {
        $total = $row['internal'] + $row['external'];
        $max = $row['internal_total'] + $row['external_total'];
        $percentage = ($total / $max) * 100;

        if ($percentage < 33) {
            $passed = false;
        }

        $row['total'] = $total;
        $row['max'] = $max;
        $row['percentage'] = $percentage;
        $marks[] = $row;
    }

    $status = $passed ? 'pass' : 'fail';
    $file_name = "marksheet_{$user_id}_sem{$semester_id}.pdf";
    $file_path = "../documents/marksheets/" . $file_name;

    // Generate PDF
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $html = "<h2>Marksheet</h2>
             <p><strong>Name:</strong> {$student['name']} {$student['last_name']}</p>
             <p><strong>Father's Name:</strong> {$student['father_name']}</p>
             <p><strong>DOB:</strong> {$student['dob']}</p>
             <p><strong>Email:</strong> {$student['email']}</p>
             <p><strong>Course:</strong> {$student['course']}</p>
             <p><strong>Semester:</strong> {$student['semester_number']}</p>
             <br><table border='1' cellpadding='4'>
             <tr>
                <th>Subject</th><th>Internal</th><th>External</th><th>Total</th><th>Max</th><th>%</th>
             </tr>";

    foreach ($marks as $m) {
        $html .= "<tr>
                    <td>{$m['subject_name']}</td>
                    <td>{$m['internal']}</td>
                    <td>{$m['external']}</td>
                    <td>{$m['total']}</td>
                    <td>{$m['max']}</td>
                    <td>" . number_format($m['percentage'], 2) . "%</td>
                  </tr>";
    }

    $html .= "</table><br><strong>Status: " . strtoupper($status) . "</strong>";

    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output($file_path, 'F'); // Save to file

    // Insert into DB
    $insert = $conn->prepare("INSERT INTO marksheet (user_id, semester_id, status, file_name, generated_at)
                              VALUES (?, ?, ?, ?, NOW())");
    $insert->bind_param("iiss", $user_id, $semester_id, $status, $file_name);
    $insert->execute();

    echo "<p>Marksheet generated successfully. <a href='$file_path' download>Download</a></p>";
    exit();
}
?>

<!-- Admin Form UI -->
<!DOCTYPE html>
<html>
<head>
    <title>Generate Marksheet</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <h2>Generate Marksheet</h2>
    <form method="POST">
        <label>Student ID:</label>
        <input type="number" name="user_id" required><br>

        <label>Semester ID:</label>
        <input type="number" name="semester_id" required><br>

        <button type="submit">Generate Marksheet</button>
    </form>
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
