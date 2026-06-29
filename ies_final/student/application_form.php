<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php?role=student");
  exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $father_name = $_POST['father_name'];
  $gender = $_POST['gender'];
  $dob = $_POST['dob'];
  $category = $_POST['category'];

  $course_id = intval($_POST['course']);
  $semester_number = intval($_POST['semester']);
  $semester_type = $_POST['semester_type'];
  $exam_type = $_POST['exam_type'];
  $subjects = $_POST['subjects'] ?? [];

  // 1. Update/insert into student_profiles
  $check = $conn->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
  $check->bind_param("i", $user_id);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE student_profiles SET father_name=?, gender=?, category=?, dob=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $father_name, $gender, $category, $dob, $user_id);
  } else {
    $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, father_name, gender, category, dob) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $father_name, $gender, $category, $dob);
  }
  $stmt->execute();

  // 2. Get semester_id
  $sem_stmt = $conn->prepare("SELECT id FROM semesters WHERE course_id = ? AND semester_number = ? AND semester_type = ?");
  $sem_stmt->bind_param("iis", $course_id, $semester_number, $semester_type);
  $sem_stmt->execute();
  $sem_stmt->bind_result($semester_id);
  $sem_stmt->fetch();
  $sem_stmt->close();

  if (!$semester_id) {
    $msg = "Invalid semester selection.";
  } else {
    // 3. Insert exam application
    $app_stmt = $conn->prepare("INSERT INTO student_exam_applications (user_id, course_id, semester_id, exam_type) VALUES (?, ?, ?, ?)");
    $app_stmt->bind_param("iiis", $user_id, $course_id, $semester_id, $exam_type);
    $app_stmt->execute();
    $application_id = $app_stmt->insert_id;

    // 4. Insert subjects
    $sub_stmt = $conn->prepare("INSERT INTO student_subjects (application_id, subject_id) VALUES (?, ?)");
    foreach ($subjects as $subject_id) {
      $sub_stmt->bind_param("ii", $application_id, $subject_id);
      $sub_stmt->execute();
    }

    $msg = "Application submitted successfully!";
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Application Form</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f5f7fa;
      padding: 40px;
    }

    form {
      max-width: 700px;
      margin: auto;
      background-color: #fff;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: 600;
      color: #333;
    }

    input[type="text"],
    input[type="date"],
    select {
      width: 100%;
      padding: 10px 12px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      font-size: 15px;
      background-color: #fefefe;
    }

    input[type="radio"] {
      margin-top: 10px;
      margin-right: 5px;
    }

    button {
      margin-top: 25px;
      width: 100%;
      padding: 12px;
      background-color: #2e7d32;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #1b5e20;
    }

    #subject-list {
      margin-top: 20px;
    }

    #subject-list div {
      margin-top: 6px;
    }

    p {
      text-align: center;
      color: green;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div>
    <a href="./dashboard.php">
      <button>Back</button>
    </a>
  </div>
  <h2>Student Exam Form</h2>
  <?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

  <form method="POST">
    <label>Father's Name:</label>
    <input type="text" name="father_name" required><br>

    <label>Gender:</label>
    <select name="gender" required>
      <option value="">Select</option>
      <option>Male</option>
      <option>Female</option>
      <option>Other</option>
    </select><br>

    <label>Category:</label>
    <select name="category" required>
      <option value="General">General</option>
      <option value="OBC">OBC</option>
      <option value="SC">SC</option>
      <option value="ST">ST</option>
      <option value="pwd">pwd</option>
      <option value="EWS">EWS</option>
      <option value="Child of Martyers">Child of Martyers</option>
      <option value="Child of Employee">Child of Employee</option>
      <option value="Child of Alumni">Child of Alumni</option>
    </select><br>

    <label>Date of Birth:</label>
    <input type="date" name="dob" required><br>

    <label>Course:</label>
    <select name="course" id="course" required>
      <option value="">Select</option>
      <?php
      $result = $conn->query("SELECT id, name FROM courses");
      while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
      }
      ?>
    </select><br>

    <label>Semester Type:</label>
    <input type="radio" name="semester_type" value="even" required onchange="updateSemester()"> Even
    <input type="radio" name="semester_type" value="odd" onchange="updateSemester()"> Odd<br>

    <label>Semester Number:</label>
    <select name="semester" id="semester" required>
      <option value="">Select Semester</option>
    </select><br>

    <label>Exam Type:</label>
    <select name="exam_type" required>
      <option value="regular">regular</option>
      <option value="back">back</option>
      <option value="improvement">improvement</option>
    </select><br>

    <div id="subject-list">
      <label>Select Subjects:</label>
      <div id="subjects"></div>
    </div>

    <button type="submit">Submit</button>
  </form>

  <script>
    function updateSemester() {
      const semester = document.getElementById('semester');
      const type = document.querySelector('input[name="semester_type"]:checked').value;
      semester.innerHTML = "";
      const list = (type === 'even') ? [2, 4, 6, 8, 10] : [1, 3, 5, 7, 9];

      list.forEach((s, i) => {
        const opt = document.createElement('option');
        opt.value = s;
        opt.textContent = s;
        semester.appendChild(opt);
      });

      // ✅ Auto-trigger subject fetch for first option
      fetchSubjects();
    }

    document.getElementById('course').addEventListener('change', fetchSubjects);
    document.getElementById('semester').addEventListener('change', fetchSubjects);

    function fetchSubjects() {
      const courseId = document.getElementById('course').value;
      const semester = document.getElementById('semester').value;
      if (!courseId || !semester) return;

      fetch(`fetch_subjects.php?course=${courseId}&semester=${semester}`)
        .then(res => res.json())
        .then(data => {
          const container = document.getElementById('subjects');
          container.innerHTML = "";
          data.forEach(subject => {
            const div = document.createElement('div');
            div.innerHTML = `<input type="checkbox" name="subjects[]" value="${subject.id}"> ${subject.subject_code} - ${subject.subject_name}`;
            container.appendChild(div);
          });
        });
    }
  </script>
</body>

</html>