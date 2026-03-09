<?php
// Make sure user is a student
if(!isRole('student')){
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

// Count the student’s submissions
$total_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=?");
$total_submissions->execute([$user_id]);
$total_submissions = $total_submissions->fetchColumn();

// Count approved submissions
$approved_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='approved'");
$approved_submissions->execute([$user_id]);
$approved_submissions = $approved_submissions->fetchColumn();

// Count pending submissions
$pending_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='pending'");
$pending_submissions->execute([$user_id]);
$pending_submissions = $pending_submissions->fetchColumn();

// Display dashboard
echo "<h2>Student Dashboard</h2>";
echo "<ul>";
echo "<li><strong>Total Submissions:</strong> $total_submissions</li>";
echo "<li><strong>Approved Submissions:</strong> $approved_submissions</li>";
echo "<li><strong>Pending Submissions:</strong> $pending_submissions</li>";
echo "</ul>";
?>