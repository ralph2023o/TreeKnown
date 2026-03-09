<?php
// Database configuration
$host = "localhost";
$dbname = "treeknown";  // Database name
$user = "root";          // Your DB username
$pass = "";              // Your DB password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
}
catch(PDOException $e) {
    echo "PDO Exception: " . $e->getMessage();
    $conn = null;
}

// Start session for all pages
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

// Function to check user role
function isRole($role){
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}
?>