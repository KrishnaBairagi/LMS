<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 55%, #f97316 100%);
            color: white; display: flex; flex-direction: column;
            justify-content: center; align-items: center; text-align: center;font-family: 'poppins', sans-serif; font-weight: 800; font-size: 26px; color: #f0f2ff; margin-bottom: 6px;
            padding: 40px; position: relative; overflow: hidden;
}
        .hero {
            height: 100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            text-align:center;
        }
        .btn-custom {
            background:white;
            color:#224abe;
            font-weight:700;
            padding:10px 25px;
            border-radius:8px;
        }
    </style>
</head>

<body>
  <div class="hero">
    <div>
        <h1 class="display-4 mb-3">📚 Library Management System</h1>
        <p class="mb-4">Manage books, users, and transactions easily</p>

        <a href="login.php" class="btn btn-custom me-2">Login</a>
        <a href="register.php" class="btn btn-outline-light">Create Account</a>
    </div>
</div>
</div>


</body>
</html>
