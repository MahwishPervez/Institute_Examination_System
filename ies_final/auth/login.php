<?php
// Include the database connection
include('../includes/db.php'); // Adjust if necessary

// Get the role from the URL
$role = $_GET['role'] ?? '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if username and password are provided
    if (!empty($email) && !empty($password)) {
        // Prepare and execute the query to check the credentials
        $stmt = $conn->prepare("SELECT `id`, `password`, `role` FROM `users` WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, now verify the password
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password']) && $user['role'] == $role) {
                // Correct credentials, start session and redirect to respective dashboards
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect to respective dashboards based on the role
                if ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } elseif ($user['role'] == 'teacher') {
                    header("Location: ../teacher/dashboard.php");
                } elseif ($user['role'] == 'student') {
                    header("Location: ../student/dashboard.php");
                }
                exit;
            } else {
                echo "Invalid password or role mismatch.";
            }
        } else {
            echo "No user found with this username.";
        }
    } else {
        echo "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login as <?= ucfirst($role) ?> | Institute Exam System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #43cea2, #185a9d);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1s ease-in;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 2rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            text-align: left;
            font-weight: 500;
            color: #e0e0e0;
        }

        input[type="email"],
        input[type="password"] {
            padding: 10px 14px;
            border-radius: 6px;
            border: none;
            outline: none;
            font-size: 1rem;
        }

        button {
            padding: 12px;
            background-color: #00c9a7;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #00a089;
        }

        p {
            margin-top: 20px;
            font-size: 0.95rem;
        }

        a {
            color: #fff;
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 500px) {
            .login-container {
                padding: 30px 20px;
            }
        }

        .back-link {
            margin-top: 20px;
            font-size: 0.95rem;
        }

        .back-link a {
            color: white;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login as <?= ucfirst($role) ?></h2>
        <form method="POST" action="">
            <label for="email">Email</label>
            <input type="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account?
            <a href="signup.php?role=<?= $role ?>">Sign up as <?= ucfirst($role) ?></a>
        </p>
        <div class="back-link">
            <a href="../index.php">&larr; Back to Module Selection</a>
        </div>
    </div>
</body>

</html>