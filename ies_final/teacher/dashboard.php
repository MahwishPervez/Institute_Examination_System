<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
  header("Location: ../auth/login.php?role=teacher");
  exit;
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Teacher Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f4f4;
      padding: 40px;
    }

    .container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }

    .card {
      margin: 15px 0;
      padding: 20px;
      background: #e3f2fd;
      border-left: 6px solid #1976d2;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .card:hover {
      background: #bbdefb;
    }

    .card a {
      text-decoration: none;
      font-weight: bold;
      color: #0d47a1;
      font-size: 18px;
    }
  </style>
</head>

<body>
  <div style="display: flex; align-items: center; justify-content: space-between; position: absolute; width: 90%;">
    <a href="../auth/logout.php"><button style="color: white; background-color: red;">Logout</button></a>
    <a href="../profile.php"><button style="color: black; background-color: yellow;">View Profile</button></a>
  </div>
  <div class="container">
    <h2>Welcome, Teacher</h2>

    <div class="card"><a href="enter_attendance.php">📅 Enter Attendance</a></div>
    <div class="card"><a href="enter_marks.php">📝 Enter Student Marks</a></div>
    <div class="card"><a href="announcements.php">📣 Make Announcement</a></div>
    <div class="card"><a href="homework.php">📚 Give Homework</a></div>
    <div class="card"><a href="assignments.php">📤 Assign Assignments</a></div>
  </div>

</body>

</html>