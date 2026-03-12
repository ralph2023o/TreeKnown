<?php
session_start();
include __DIR__ . '/config/db.php';

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check user with approved status
    $stmt = $conn->prepare("SELECT * FROM USERS WHERE email=? AND password=? AND status='approved'");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user){
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect by role
        switch($user['role']){
            case 'admin':
                header("Location: Admin_Tabs/dashboard.php"); break;
            case 'teacher':
                header("Location: Instructor_Tabs/dashboard.php"); break;
            case 'student':
                header("Location: Student_Tabs/dashboard.php"); break;
        }
        exit;
    } else {
        $error = "Invalid email/password or account not approved yet.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TreeKnown - Login</title>
<style>
/* Same admin-themed design as before */
*{ box-sizing:border-box;margin:0;padding:0;font-family:Arial,sans-serif; }
body{ display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f6f9; }
.login-container{ background:white; width:380px; padding:40px 30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.2); text-align:center;}
.login-container h1{ color:#2e8b57; margin-bottom:25px; font-size:28px;}
.login-container input{ width:100%; padding:12px 15px; margin-bottom:20px; border:1px solid #ccc; border-radius:8px; font-size:16px;}
.login-container input:focus{ border-color:#36A2EB; outline:none;}
.login-btn{ width:100%; padding:12px; background:#36A2EB; color:white; border:none; border-radius:8px; font-size:16px; cursor:pointer; margin-bottom:10px; font-weight:bold;}
.login-btn:hover{ background:#2e8b57;}
.register-btn{ width:100%; padding:12px; background:white; color:#36A2EB; border:2px solid #36A2EB; border-radius:8px; font-size:16px; cursor:pointer;}
.register-btn:hover{ background:#36A2EB; color:white;}
.error{ margin-top:15px; color:red; font-size:14px;}
</style>
</head>
<body>
<div class="login-container">
<h1>🌳 TreeKnown Login</h1>
<form method="post">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit" name="login" class="login-btn">Login</button>
</form>

<form action="register.php" method="get">
<button type="submit" class="register-btn">Register</button>
</form>

<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>