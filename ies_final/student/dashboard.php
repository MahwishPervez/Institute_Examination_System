<?php
session_start();
// Include database connection
require '../includes/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php?role=student");
  exit;
}
$studentName = $_SESSION['name'] ?? 'Student';

$user_id = $_SESSION['user_id'];


// Fetch admit card if it exists
$sql = "SELECT ac.file_name, ac.semester_id, s.semester_number
        FROM admit_cards ac
        JOIN semesters s ON ac.semester_id = s.id
        WHERE ac.user_id = ?
        ORDER BY ac.issued_at DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$admit_card = $result->fetch_assoc();


// Get course ID student applied to (assuming 1 active application)
$appQuery = $conn->prepare("
    SELECT course_id FROM student_exam_applications
    WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
");
$appQuery->bind_param("i", $user_id);
$appQuery->execute();
$appRes = $appQuery->get_result()->fetch_assoc();

$course_id = $appRes['course_id'] ?? null;

$announcements = $homeworks = $assignments = [];

if ($course_id) {
    // Fetch announcements
    $annQuery = $conn->prepare("SELECT title, message, created_at FROM announcements WHERE course_id = ? ORDER BY created_at DESC");
    $annQuery->bind_param("i", $course_id);
    $annQuery->execute();
    $announcements = $annQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch homeworks
    $homeQuery = $conn->prepare("SELECT title, description, due_date FROM homeworks WHERE course_id = ? ORDER BY due_date ASC");
    $homeQuery->bind_param("i", $course_id);
    $homeQuery->execute();
    $homeworks = $homeQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch assignments
    $assQuery = $conn->prepare("SELECT title, description, due_date FROM assignments WHERE course_id = ? ORDER BY due_date ASC");
    $assQuery->bind_param("i", $course_id);
    $assQuery->execute();
    $assignments = $assQuery->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <title>Student Dashboard</title>
</head>

<body>
  <div style="position: absolute; display: flex; align-items: center; justify-content: space-between; width: 90%;">
    <a href="../auth/logout.php"><button style="color: white; background-color: red;">Logout</button></a>
    <a href="../profile.php"><button style="color: black; background-color: yellow;">View Profile</button></a>
  </div>
  <div class="container" style="margin-top: 50px;">
    <h2>Welcome, <?php echo htmlspecialchars($studentName); ?></h2>
    <div class="dashboard">
      <ul>
        <li>
          <a href="application_form.php">
            <div class="module-box">
              <h3>Fill Application Form</h3>
            </div>
          </a>
        </li>

        <li>
          <div class="module-box">
            <h3>Your Admit Card</h3>
            <?php if ($admit_card): ?>
              <p>Admit card for Semester <?= $admit_card['semester_number'] ?> is available.</p>
              <a href="../documents/admit_cards/<?= htmlspecialchars($admit_card['file_name']) ?>" download>Download Admit Card</a>
            <?php else: ?>
              <p>Your admit card is not yet available. Please check back later.</p>
            <?php endif; ?>
          </div>
        </li>

        <li>
          <div class="module-box">
            <h3>Download Marksheet</h3>
            <?php if (!empty($marksheet)): ?>
              <p><a href="download_document.php?type=marksheet" target="_blank">Download Marksheet</a></p>
            <?php else: ?>
              <p>Marksheet not generated yet.</p>
            <?php endif; ?>
          </div>
        </li>

        <li>
          <div class="module-box">
            <h3>Download Migration</h3>
            <?php if (!empty($migration)): ?>
              <p><a href="download_document.php?type=migration" target="_blank">Download Migration Certificate</a></p>
            <?php else: ?>
              <p>Migration Certificate not generated yet.</p>
            <?php endif; ?>
          </div>
        </li>

        <li>
          <a href="attendance.php">
            <div class="module-box">
              <h3>View Attendance</h3>
            </div>
          </a>
        </li>
      </ul>
      <h3>📢 Announcements</h3>
    <?php if ($announcements): ?>
        <ul>
            <?php foreach ($announcements as $a): ?>
                <li>
                    <strong><?= htmlspecialchars($a['title']) ?>:</strong>
                    <?= nl2br(htmlspecialchars($a['message'])) ?>
                    <br><small>Posted on <?= $a['created_at'] ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No announcements yet.</p>
    <?php endif; ?>

    <h3>📝 Assignments</h3>
    <?php if ($assignments): ?>
        <ul>
            <?php foreach ($assignments as $as): ?>
                <li>
                    <strong><?= htmlspecialchars($as['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($as['description'])) ?><br>
                    <em>Due: <?= $as['due_date'] ?></em>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No assignments available.</p>
    <?php endif; ?>

    <h3>📚 Homeworks</h3>
    <?php if ($homeworks): ?>
        <ul>
            <?php foreach ($homeworks as $h): ?>
                <li>
                    <strong><?= htmlspecialchars($h['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($h['description'])) ?><br>
                    <em>Due: <?= $h['due_date'] ?></em>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No homeworks available.</p>
    <?php endif; ?>
    </div>

  </div>
</body>

</html>