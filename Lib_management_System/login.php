<?php
session_start();
include 'config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user'] = $user['name'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'] ?? 'user';

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Incorrect password";
        }

    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Library Management</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap');

body { height: 100vh; margin: 0; background: #0d0f1a; color: #f0f2ff; font-family: 'Plus Jakarta Sans', sans-serif; }
.container-fluid { height: 100vh; }

.left-side {
    background: linear-gradient(135deg, #7c3aed 0%, #ec4899 55%, #f97316 100%);
    color: white; display: flex; flex-direction: column;
    justify-content: center; align-items: center; text-align: center;
    padding: 40px; position: relative; overflow: hidden;
}
.left-side::before { content: ''; position: absolute; top: -80px; right: -80px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,.1); }
.left-side::after { content: ''; position: absolute; bottom: -60px; left: -60px; width: 250px; height: 250px; border-radius: 50%; background: rgba(255,255,255,.07); }
.left-side h1 { font-family: 'poppins', sans-serif; font-weight: 800; font-size: 46px; margin-bottom: 20px; position: relative; z-index: 1; }
.left-side p { font-size: 18px; opacity: .9; position: relative; z-index: 1; }
.library-icon { font-size: 80px; margin-bottom: 20px; position: relative; z-index: 1; }

.right-side { background: linear-gradient(135deg, #0d0f1a, #13162b); display: flex; justify-content: center; align-items: center; }

.login-card {
    width: 400px; padding: 44px; border-radius: 20px;
    background: rgba(19,22,43,.92);
    box-shadow: 0 24px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(124,58,237,.25);
    text-align: center; backdrop-filter: blur(20px);
    position: relative; animation: fadeInUp .6s ease;
}
.login-card::before {
    content: ''; position: absolute; top: 0; left: 10%; right: 10%; height: 3px;
    border-radius: 0 0 6px 6px;
    background: linear-gradient(90deg, #7c3aed, #ec4899, #14b8a6);
}
.login-card h3 { font-family: 'poppins', sans-serif; font-weight: 800; font-size: 26px; color: #f0f2ff; margin-bottom: 6px; }
.login-card > p { color: #7580a0; font-size: 14px; }

.form-control {
    border-radius: 10px; background-color: rgba(20,20,40,.7);
    border: 2px solid #2a2d4a; color: #f0f2ff; padding: 13px 16px;
    font-family: 'Plus Jakarta Sans', sans-serif; font-size: 14px; width: 100%; transition: all .3s ease;
}
.form-control:focus { background-color: rgba(26,29,53,.9); border-color: #7c3aed; color: #f0f2ff; box-shadow: 0 0 0 3px rgba(124,58,237,.15); outline: none; }
.form-control::placeholder { color: #7580a0; }

.form-label { color: #b0b8d8; font-weight: 600; font-size: 13px; text-align: left; display: block; margin-bottom: 8px; letter-spacing: .3px; }
.mb-3 { margin-bottom: 18px; }
.mb-4 { margin-bottom: 26px; }

.btn-primary {
    border-radius: 10px;
    background: linear-gradient(135deg, #7c3aed, #ec4899);
    border: none; padding: 13px 20px; font-weight: 700;
    font-family: 'Plus Jakarta Sans', sans-serif; transition: all .3s;
    box-shadow: 0 4px 16px rgba(124,58,237,.4); color: white;
    text-transform: uppercase; letter-spacing: .7px; font-size: 13px;
}
.btn-primary:hover { background: linear-gradient(135deg, #5b21b6, #be185d); transform: translateY(-3px); box-shadow: 0 10px 28px rgba(124,58,237,.5); color: white; }
.w-100 { width: 100%; }

.alert-danger { background: rgba(244,63,94,.1); border: 1px solid rgba(244,63,94,.35); color: #fda4af; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; }

.password-toggle { cursor: pointer; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #7580a0; font-size: 18px; transition: color .2s; }
.password-toggle:hover { color: #a78bfa; }
.position-relative { position: relative; }

.text-center a { color: #a78bfa; text-decoration: none; font-weight: 600; }
.text-center a:hover { color: #f9a8d4; text-decoration: underline; }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: translateY(0); } }
@media (max-width: 768px) { .left-side { display: none; } .login-card { width: 90%; max-width: 400px; } }
</style>

</head>

<body>

<div class="container-fluid">
<div class="row h-100">

<!-- Left Side -->
<div class="col-md-6 left-side d-none d-md-flex">
    <div>
        <h1>📚 Library<br>Management</h1>
        <p class="mt-3">Manage books and users easily with our modern system.</p>
    </div>
</div>

<!-- Right Side -->
<div class="col-md-6 right-side">

<div class="login-card">

<h3 class="mb-3 text-center">Welcome Back</h3>
<p class="text-center text-white mb-4">Login to your account</p>

<?php if($error): ?>
<div class="alert alert-danger mb-3">
    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
</div>

<div class="mb-3">
    <label class="form-label">Password</label>
    <div class="position-relative">
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        <span class="password-toggle" onclick="togglePassword()"><i class="fas fa-eye"></i></span>
    </div>
</div>

<button type="submit" class="btn btn-primary w-100 mb-3">
    <i class="fas fa-sign-in-alt me-2"></i>Login
</button>

</form>

<div class="text-center">
    Don't have an account? <a href="register.php">Create one now</a>
</div>

</div>

</div>

</div>
</div>

<script>
function togglePassword(){
    const pass=document.getElementById("password");
    pass.type=pass.type==="password"?"text":"password";
}
</script>

</body>
</html>
