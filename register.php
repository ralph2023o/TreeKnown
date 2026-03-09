<?php
session_start();
include __DIR__ . '/config/db.php';

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'student' or 'teacher'
    $student_id = $_POST['student_id'] ?? null;

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM USERS WHERE email=?");
    $stmt->execute([$email]);
    if($stmt->fetch()){
        $error = "Email already registered.";
    } else {
        // Insert new user as pending
        $stmt = $conn->prepare("INSERT INTO USERS (name,email,password,role,student_id,status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$name,$email,$password,$role,$student_id]);
        $success = "Registration successful! Wait for admin approval.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown Registration</title>
    <style>
        body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background: #e0f7e9;}
        .container { background:#fff; padding:30px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.2); width:350px; }
        input, select, button { width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ccc; }
        button { background:#36A2EB; color:#fff; border:none; cursor:pointer; }
        button:hover { background:#2E8B57; }
        .success { color:green; }
        .error { color:red; }
    </style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>
        <input type="text" name="student_id" placeholder="Student ID (optional)">
        <button type="submit" name="register">Register</button>
    </form>
    <?php 
    if(isset($error)) echo "<p class='error'>$error</p>";
    if(isset($success)) echo "<p class='success'>$success</p>";
    ?>
</div>
</body>
</html>