<?php
require '../includes/db.php';

$role = $_GET['role'] ?? '';  // Get role from URL (student, teacher, admin)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert new user into the database with the role
    $stmt = $conn->prepare("INSERT INTO `users` (`name`, `last_name`, `email`, `password`, `role`) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $last_name, $email, $password, $role);

    if ($stmt->execute()) {
        // Redirect to login page after successful signup
        header("Location: login.php?role=$role");
        exit;
    } else {
        $error = "Signup failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup as <?= ucfirst($role) ?> | Institute Exam System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #dfe9f3, #ffffff);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #333;
      animation: fadeIn 0.8s ease;
    }

    .signup-container {
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 450px;
      text-align: center;
    }

    h2 {
      margin-bottom: 20px;
      font-size: 1.8rem;
      color: #2c3e50;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    label {
      text-align: left;
      font-weight: 500;
      color: #444;
    }

    input {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 1rem;
    }

    button {
      padding: 12px;
      background-color: #007BFF;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }

    .error-message {
      color: #721c24;
      background-color: #f8d7da;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 15px;
    }

    .back-link {
      margin-top: 20px;
      font-size: 0.95rem;
    }

    .back-link a {
      color: #007BFF;
      text-decoration: none;
    }

    .back-link a:hover {
      text-decoration: underline;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(15px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 500px) {
      .signup-container {
        padding: 30px 20px;
      }
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2>Signup as <?= ucfirst($role) ?></h2>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="name">First-Name</label>
      <input type="text" name="name" required>

      <label for="last_name">Last-Name</label>
      <input type="text" name="last_name" required>

      <label for="email">Email</label>
      <input type="email" name="email" required>

      <label for="password">Password</label>
      <input type="password" name="password" required>

      <button type="submit">Signup</button>
    </form>

    <div class="back-link">
      Already have an account? <a href="login.php?role=<?= $role ?>">Back to Login</a>
    </div>
  </div>
</body>
</html>
