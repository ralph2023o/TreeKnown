<?php
session_start();
include __DIR__ . '/config/db.php';

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM USERS WHERE email=? AND password=?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user){
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect based on role
        if($user['role'] == 'admin'){
            header("Location: Admin_Tabs/dashboard.php");
        } elseif($user['role'] == 'teacher'){
            header("Location: Instructor_Tabs/dashboard.php");
        } else {
            header("Location: Student_Tabs/dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
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
    /* Reset some default styles */
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }

    body {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(to right, #36A2EB, #3CB371);
    }

    .login-container {
        background: #fff;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        width: 350px;
        text-align: center;
    }

    .login-container h1 {
        margin-bottom: 30px;
        color: #2E8B57;
        font-size: 28px;
    }

    .login-container input[type="email"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .login-container input[type="email"]:focus,
    .login-container input[type="password"]:focus {
        border-color: #36A2EB;
        outline: none;
    }

    .login-container button {
        width: 100%;
        padding: 12px;
        background: #36A2EB;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .login-container button:hover {
        background: #2E8B57;
    }

    .error {
        margin-top: 15px;
        color: red;
        font-size: 14px;
    }

    @media (max-width: 400px) {
        .login-container {
            width: 90%;
            padding: 30px 20px;
        }
    }
</style>
</head>
<body>

<div class="login-container">
    <h1>TreeKnown Login</h1>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>