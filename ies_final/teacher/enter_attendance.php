<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
  header("Location: ../auth/login.php?role=teacher");
  exit;
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// Get teacher's course
$stmt = $conn->prepare("SELECT course_id FROM course_teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
  die("You are not assigned to any course.");
}

$course_id = $course['course_id'];

// Handle attendance form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['semester_id'])) {
  $semester_id = $_POST['semester_id'];
  $data = $_POST['attendance'];

  foreach ($data as $user_id => $entry) {
    $attended = (int)$entry['attended'];
    $total = (int)$entry['total'];
    $percentage = $total > 0 ? round(($attended / $total) * 100, 2) : 0.00;

    // Check if already exists
    $check = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND semester_id = ?");
    $check->bind_param("ii", $user_id, $semester_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      // Update
      $stmt = $conn->prepare("UPDATE attendance SET attended = ?, total_classes = ?, attendance_percentage = ? WHERE user_id = ? AND semester_id = ?");
      $stmt->bind_param("iidii", $attended, $total, $percentage, $user_id, $semester_id);
    } else {
      // Insert
      $stmt = $conn->prepare("INSERT INTO attendance (user_id, semester_id, attended, total_classes, attendance_percentage) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("iiiid", $user_id, $semester_id, $attended, $total, $percentage);
    }
    $stmt->execute();
  }

  $msg = "Attendance submitted successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Enter Attendance</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f0f0;
      padding: 30px;
    }

    form {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #333;
    }

    label, select {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    select, input[type="number"] {
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

    th, td {
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
    <a href="./dashboard.php"><button>Back</button></a>
</div>
  <form method="POST">
    <h2>Enter Student Attendance</h2>

    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <label>Select Semester:</label>
    <select name="semester_id" onchange="this.form.submit()" required>
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
      <table>
        <tr>
          <th>Student Name</th>
          <th>Classes Attended</th>
          <th>Total Classes Held</th>
        </tr>
        <?php
          $semester_id = $_POST['semester_id'];

          $stmt = $conn->prepare("
            SELECT u.id, u.name, u.last_name
            FROM student_exam_applications sea
            JOIN users u ON sea.user_id = u.id
            WHERE sea.course_id = ? AND sea.semester_id = ?
          ");
          $stmt->bind_param("ii", $course_id, $semester_id);
          $stmt->execute();
          $students = $stmt->get_result();

          while ($row = $students->fetch_assoc()) {
            $uid = $row['id'];
            $fullName = $row['name'] . ' ' . $row['last_name'];
            echo "
              <tr>
                <td>$fullName</td>
                <td><input type='number' name='attendance[$uid][attended]' min='0' required></td>
                <td><input type='number' name='attendance[$uid][total]' min='1' required></td>
              </tr>
            ";
          }
        ?>
      </table>

      <button type="submit">Save Attendance</button>
    <?php endif; ?>
  </form>

</body>
</html>
