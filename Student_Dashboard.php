<?php
include __DIR__ . '/config/db.php';

// Ensure user is a student
if(!isRole('student')){
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student'; // optional display

// Count total submissions by this student
$total_submissions_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=?");
$total_submissions_stmt->execute([$user_id]);
$total_submissions = $total_submissions_stmt->fetchColumn();

// Count approved submissions
$approved_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='approved'");
$approved_stmt->execute([$user_id]);
$approved_submissions = $approved_stmt->fetchColumn();

// Count pending submissions
$pending_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='pending'");
$pending_stmt->execute([$user_id]);
$pending_submissions = $pending_stmt->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - Student Dashboard</title>
    <style>
        body { font-family: Arial; margin:0; background:#f4f4f4; }
        header { background:#228B22; color:white; padding:15px; text-align:center; }
        nav { background:#2E8B57; display:flex; justify-content:center; }
        nav a { color:white; text-decoration:none; margin:0 10px; padding:10px; }
        nav a:hover { background:#3CB371; border-radius:5px; }
        .container { padding:20px; }
        .card { background:white; padding:15px; margin:10px 0; border-radius:5px; }
    </style>
</head>
<body>
<header>
    <h1>TreeKnown - Student Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($user_name) ?></p>
</header>

<nav>
    <a href="student_dashboard.php">Dashboard</a>
    <a href="Student/submit_photo.php">Submit Tree</a>
    <a href="Student/library.php">Tree Library</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Your Stats</h2>
    <div class="card"><strong>Total Submissions:</strong> <?= $total_submissions ?></div>
    <div class="card"><strong>Approved:</strong> <?= $approved_submissions ?></div>
    <div class="card"><strong>Pending:</strong> <?= $pending_submissions ?></div>
</div>
</body>
</html>