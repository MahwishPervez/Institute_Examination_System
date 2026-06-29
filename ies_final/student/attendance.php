<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT s.semester_number, s.semester_type, a.attended, a.total_classes, a.attendance_percentage
        FROM attendance a
        JOIN semesters s ON a.semester_id = s.id
        WHERE a.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$attendance = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Attendance</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div>
    <a href="./dashboard.php">
      <button>Back</button>
    </a>
  </div>
    <h2>My Attendance Record</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Semester</th>
            <th>Type</th>
            <th>Attended</th>
            <th>Total</th>
            <th>Percentage</th>
        </tr>
        <?php while ($row = $attendance->fetch_assoc()): ?>
            <tr>
                <td><?= $row['semester_number'] ?></td>
                <td><?= ucfirst($row['semester_type']) ?></td>
                <td><?= $row['attended'] ?></td>
                <td><?= $row['total_classes'] ?></td>
                <td><?= $row['attendance_percentage'] ?>%</td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
