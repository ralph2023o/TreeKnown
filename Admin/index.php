<?php
session_start();
include 'db.php';

// Only admin can access
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'){
    die("Access Denied");
}

// Tab selection
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - Admin Panel Prototype</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f4f4f4; }
        header { background: #228B22; color: #fff; padding: 15px; text-align: center;}
        nav { display: flex; background: #2E8B57; }
        nav a { flex:1; padding: 10px; text-align: center; color: #fff; text-decoration: none;}
        nav a.active { background: #3CB371; }
        .container { padding: 20px; }
        ul { list-style: none; padding: 0; }
        li { background: #fff; margin:5px 0; padding:10px; border-radius:5px; box-shadow:0 2px 3px rgba(0,0,0,0.1);}
    </style>
</head>
<body>
<header>
    <h1>TreeKnown - Admin Panel (Prototype)</h1>
</header>
<nav>
    <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="?tab=users" class="<?= $tab=='users'?'active':'' ?>">Users</a>
    <a href="?tab=verification" class="<?= $tab=='verification'?'active':'' ?>">Tree Verification</a>
    <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
</nav>
<div class="container">
<?php
switch($tab){
    case 'dashboard':
        $total_students = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='student'")->fetchColumn();
        $total_teachers = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='teacher'")->fetchColumn();
        $total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();
        $pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
        echo "<h2>Dashboard</h2>";
        echo "<ul>
            <li>Total Students: $total_students</li>
            <li>Total Teachers: $total_teachers</li>
            <li>Total Trees: $total_trees</li>
            <li>Pending Trees: $pending_trees</li>
        </ul>";
        break;

    case 'users':
        $users = $conn->query("SELECT name,email,role,student_id FROM USERS")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Users Management</h2><ul>";
        foreach($users as $u){
            echo "<li>{$u['name']} ({$u['role']}) - {$u['email']}</li>";
        }
        echo "</ul>";
        break;

    case 'verification':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, u.name AS submitted_by
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='pending'
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Pending Tree Verification</h2><ul>";
        foreach($trees as $t){
            echo "<li>Tree ID {$t['tree_id']} - {$t['species_name']} submitted by {$t['submitted_by']} 
            [<a href='verify_tree.php?id={$t['tree_id']}&action=approve'>Approve</a>] 
            [<a href='verify_tree.php?id={$t['tree_id']}&action=reject'>Reject</a>]</li>";
        }
        echo "</ul>";
        break;

    case 'library':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='approved'
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Tree Library (Approved)</h2><ul>";
        foreach($trees as $t){
            echo "<li>Tree ID {$t['tree_id']} - {$t['species_name']} at {$t['location_name']} (Submitted by {$t['submitted_by']})</li>";
        }
        echo "</ul>";
        break;

    default:
        echo "<p>Tab not found</p>";
}
?>
</div>
</body>
</html>