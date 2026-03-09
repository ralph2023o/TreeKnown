<?php
// Make sure db.php is already included in admin_panel.php

// Get total students
$total_students = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='student'")->fetchColumn();

// Get total teachers
$total_teachers = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='teacher'")->fetchColumn();

// Get total trees submitted
$total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();

// Get pending trees
$pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();

// Display Dashboard
echo "<h2>Admin Dashboard</h2>";
echo "<ul>";
echo "<li><strong>Total Students:</strong> $total_students</li>";
echo "<li><strong>Total Teachers:</strong> $total_teachers</li>";
echo "<li><strong>Total Trees Submitted:</strong> $total_trees</li>";
echo "<li><strong>Pending Trees:</strong> $pending_trees</li>";
echo "</ul>";
?>