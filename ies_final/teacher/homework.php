<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php?role=teacher");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// Fetch assigned course
$stmt = $conn->prepare("SELECT course_id FROM course_teachers WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("You are not assigned to any course.");
}

$course_id = $row['course_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $due = $_POST['due_date'];

    $stmt = $conn->prepare("INSERT INTO homeworks (teacher_id, course_id, title, description, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $teacher_id, $course_id, $title, $desc, $due);
    $stmt->execute();

    $msg = "Homework posted successfully.";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Post Homework</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 30px;
        }

        form {
            background: #fff;
            padding: 25px;
            max-width: 700px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .msg {
            color: green;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div>
        <a href="./dashboard.php">
            <button>Back</button>
        </a>
    </div>
    <form method="POST">
        <h2>Post Homework</h2>
        <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

        <label>Title:</label>
        <input type="text" name="title" required>

        <label>Description:</label>
        <textarea name="description" rows="5" required></textarea>

        <label>Due Date:</label>
        <input type="date" name="due_date" id="due_date" />
        <button type="submit">Submit</button>
    </form>
</body>

</html>