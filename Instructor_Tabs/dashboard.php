<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Only teachers can access
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'teacher'){
    die("Access Denied");
}

// Tab selection via GET
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - Teacher Dashboard</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f4f4f4; }
        header { background: #228B22; color: #fff; padding: 15px; text-align: center;}
        nav { display: flex; background: #2E8B57; }
        nav a { flex:1; padding: 10px; text-align: center; color: #fff; text-decoration: none;}
        nav a.active { background: #3CB371; }
        ul { list-style: none; padding: 0; }
        li { background: #fff; margin:5px 0; padding:10px; border-radius:5px; box-shadow:0 2px 3px rgba(0,0,0,0.1);}
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>
<header>
    <h1>TreeKnown - Teacher Dashboard</h1>
</header>

<nav style="display:flex; align-items:center; background:#2E8B57;">
    <div style="display:flex; flex:1;">
        <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="?tab=verification" class="<?= $tab=='verification'?'active':'' ?>">Tree Verification</a>
        <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
    </div>
    <a href="../logout.php" style="color:red;">Logout</a>
</nav>
<div class="container">
<?php
switch($tab){

    // ----- Dashboard Tab -----
    case 'dashboard':
        $total_pending = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
        $total_approved = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='approved'")->fetchColumn();
        echo "<h2>Dashboard Summary</h2>";
        echo "<ul>
            <li>Pending Trees: $total_pending</li>
            <li>Approved Trees: $total_approved</li>
        </ul>";
        break;

    // ----- Verification Tab -----
    case 'verification':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, u.name AS submitted_by
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='pending'
            ORDER BY t.date_submitted DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Pending Tree Verification</h2>";

        if(count($trees) > 0){
            echo "<ul>";
            foreach($trees as $t){
                echo "<li>Tree ID {$t['tree_id']} - {$t['species_name']} submitted by {$t['submitted_by']} 
                [<a href='../admin_tabs/verify_tree.php?id={$t['tree_id']}&action=approve'>Approve</a>] 
                [<a href='../admin_tabs/verify_tree.php?id={$t['tree_id']}&action=reject'>Reject</a>]</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No pending trees.</p>";
        }
        break;

    // ----- Library Tab -----
    case 'library':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='approved'
            ORDER BY t.date_submitted DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Tree Library (Approved)</h2>";

        if(count($trees) > 0){
            echo "<ul>";
            foreach($trees as $t){
                echo "<li>Tree ID {$t['tree_id']} - {$t['species_name']} at {$t['location_name']} (Submitted by {$t['submitted_by']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No approved trees in library yet.</p>";
        }
        break;

    default:
        echo "<p>Tab not found.</p>";
}
?>
</div>
</body>
</html>