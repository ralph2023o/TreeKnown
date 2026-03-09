<?php
// Total students
$total_students = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='student'")->fetchColumn();

// Total trees submitted
$total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();

// Pending trees for verification
$pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();

// Display Dashboard
echo "<h2>Teacher Dashboard</h2>";
echo "<ul>";
echo "<li><strong>Total Students:</strong> $total_students</li>";
echo "<li><strong>Total Trees Submitted:</strong> $total_trees</li>";
echo "<li><strong>Pending Trees to Verify:</strong> $pending_trees</li>";
echo "</ul>";
?>