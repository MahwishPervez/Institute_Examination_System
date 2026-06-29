<?php
require '../includes/db.php';

$course_id = intval($_GET['course']);
$semester_number = intval($_GET['semester']);
$subjects = [];

$stmt = $conn->prepare("SELECT s.id FROM semesters s WHERE s.course_id = ? AND s.semester_number = ?");
$stmt->bind_param("ii", $course_id, $semester_number);
$stmt->execute();
$stmt->bind_result($semester_id);
$stmt->fetch();
$stmt->close();

if ($semester_id) {
  $query = $conn->prepare("SELECT id, subject_code, subject_name FROM subjects WHERE course_id = ? AND semester_id = ?");
  $query->bind_param("ii", $course_id, $semester_id);
  $query->execute();
  $result = $query->get_result();
  while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
  }
  $query->close();
}

header('Content-Type: application/json');
echo json_encode($subjects);
?>
