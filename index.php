<?php
include __DIR__ . '/config/db.php';

if(isset($conn)){
    echo "DB connected!";
} else {
    echo "DB connection is NULL!";
}

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
            header("Location: admin_panel.php");
        } elseif($user['role'] == 'teacher'){
            header("Location: teacher_tabs/dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - Login</title>
</head>
<body>
    <h1>TreeKnown Login</h1>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>