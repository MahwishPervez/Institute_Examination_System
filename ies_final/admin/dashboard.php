<?php
include('../includes/db.php');
session_start();

// Optional: Add session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/style.css">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        .section {
            margin-bottom: 2rem;
        }
        .section h3 {
            color: #555;
            margin-bottom: 0.5rem;
        }
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .button-group a {
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .button-group a:hover {
            background-color: #0056b3;
        }
        .logout {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <h2>Welcome, Admin</h2>
        <div>
        <a href="../profile.php"><button style="color: black; background-color: yellow;">View Profile</button></a>
        </div>
    <div class="section">
        <h3>🎓 Academic Management</h3>
        <div class="button-group">
            <a href="manage_courses.php">Manage Courses</a>
            <a href="manage_semesters.php">Manage Semesters</a>
            <a href="manage_subjects.php">Manage Subjects</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="manage_teachers.php">Manage Teachers</a>
        </div>
    </div>

    <div class="section">
        <h3>🗂 Document Generation</h3>
        <div class="button-group">
            <a href="generate_admit_cards.php">Generate Admit Card</a>
            <a href="generate_marksheets.php">Generate Marksheet</a>
            <a href="generate_migration.php">Generate Migration</a>
        </div>
    </div>

    <div class="section">
        <h3>📢 Announcements</h3>
        <div class="button-group">
            <a href="make_announcement.php">Make Announcement</a>
            <a href="view_announcements.php">View All Announcements</a>
        </div>
    </div>

    <div class="logout">
        <a href="../auth/logout.php" style="color: red;"><button style="color: white; background-color: red;">Logout</button></a>
    </div>
</body>
</html>
