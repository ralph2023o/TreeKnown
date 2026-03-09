<?php
include 'config/db.php';

// Make sure user is logged in and is admin or teacher
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin','teacher'])){
    die("Access Denied");
}

if(isset($_GET['id']) && isset($_GET['action'])){
    $tree_id = intval($_GET['id']);
    $action = $_GET['action'];

    if($action == 'approve'){
        $stmt = $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='approved', verified_by=? WHERE tree_id=?");
        $stmt->execute([$_SESSION['user_id'], $tree_id]);
    } elseif($action == 'reject'){
        $stmt = $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='rejected', verified_by=? WHERE tree_id=?");
        $stmt->execute([$_SESSION['user_id'], $tree_id]);
    }
}

// Redirect back to the referring page (admin or teacher)
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>