<?php
require '../includes/db.php';

if (!isset($_GET['course_id'])) {
    echo json_encode([]);
    exit;
}

$course_id = (int)$_GET['course_id'];
$stmt = $conn->prepare("SELECT id, semester_number, semester_type FROM semesters WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$res = $stmt->get_result();

$semesters = [];
while ($row = $res->fetch_assoc()) {
    $semesters[] = $row;
}

header('Content-Type: application/json');
echo json_encode($semesters);
