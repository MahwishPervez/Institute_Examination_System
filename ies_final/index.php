<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Select Module | Institute Exam System</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    /* You can move this to styles.css later */

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #fff;
      animation: fadeIn 1.5s ease-in-out;
    }

    h2 {
      font-size: 2.5rem;
      margin-bottom: 30px;
      text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
    }

    .module-links {
      display: flex;
      flex-direction: column;
      gap: 20px;
      align-items: center;
      width: 100%;
      max-width: 300px;
    }

    .module-links a {
      display: block;
      width: 100%;
      text-align: center;
      padding: 15px;
      font-size: 1.2rem;
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      text-decoration: none;
      backdrop-filter: blur(10px);
      transition: all 0.3s ease-in-out;
    }

    .module-links a:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: translateY(-5px);
    }

    @keyframes fadeIn {
      0% {
        opacity: 0;
        transform: translateY(-20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (max-width: 500px) {
      h2 {
        font-size: 2rem;
      }

      .module-links a {
        font-size: 1rem;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
  <h2>Select Module</h2>
  <div class="module-links">
    <a href="auth/login.php?role=student">Student</a>
    <a href="auth/login.php?role=teacher">Teacher</a>
    <a href="auth/login.php?role=admin">Admin</a>
  </div>
</body>
</html>
