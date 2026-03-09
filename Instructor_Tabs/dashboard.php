<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Only teachers can access
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'teacher'){
    die("Access Denied");
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TreeKnown - Teacher </title>
<style>
    /* === General Styles === */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #e0f7e9;
    }
    header {
        background: #228B22;
        color: white;
        padding: 20px;
        text-align: center;
    }
    nav {
        background: #2E8B57;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        padding: 5px 10px;
    }
    nav a {
        color: white;
        text-decoration: none;
        margin: 5px 10px;
        padding: 10px 15px;
        border-radius: 5px;
        transition: background 0.3s;
    }
    nav a.active, nav a:hover {
        background: #3CB371;
    }
    .container {
        padding: 20px;
        max-width: 1200px;
        margin: auto;
    }
    h2 { color: #228B22; margin-bottom: 15px; }

    /* === Dashboard Cards === */
    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .card {
        flex: 1 1 250px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        padding: 20px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .card strong { font-size: 18px; color: #36A2EB; }

    /* === Verification List === */
    ul {
        list-style: none;
        padding: 0;
    }
    li {
        background: #fff;
        margin:5px 0;
        padding:10px 15px;
        border-radius:8px;
        box-shadow:0 4px 8px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    li:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    li a {
        margin-left: 10px;
        text-decoration: none;
        color: #36A2EB;
        font-weight: bold;
    }
    .status-pending { color: orange; font-weight: bold; }
    .status-approved { color: green; font-weight: bold; }
    .status-rejected { color: red; font-weight: bold; }

    /* === Library Cards === */
    .tree-library {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .tree-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        width: 220px;
        padding: 15px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .tree-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .tree-card img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .tree-card h3 {
        margin: 5px 0;
        font-size: 18px;
        color: #36A2EB;
    }
    .tree-card p {
        margin: 2px 0;
        font-size: 14px;
        color: #555;
    }
</style>
</head>
<body>
<header>
    <h1>TreeKnown - Teacher </h1>
</header>

<nav>
    <div style="display:flex; flex-wrap:wrap;">
        <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="?tab=verification" class="<?= $tab=='verification'?'active':'' ?>">Tree Verification</a>
        <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
    </div>
    <a href="../logout.php" style="color:red;">Logout</a>
</nav>

<div class="container">
<?php
switch($tab){

    // ----- Dashboard -----
    case 'dashboard':
        $total_pending = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
        $total_approved = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='approved'")->fetchColumn();

        echo "<h2>Dashboard Summary</h2>";
        echo '<div class="card-container">';
        echo "<div class='card'><strong>Pending Trees</strong><p>$total_pending</p></div>";
        echo "<div class='card'><strong>Approved Trees</strong><p>$total_approved</p></div>";
        echo '</div>';
        break;

    // ----- Verification -----
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
                [<a href='../verify_tree.php?id={$t['tree_id']}&action=approve'>Approve</a>] 
                [<a href='../verify_tree.php?id={$t['tree_id']}&action=reject'>Reject</a>]</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No pending trees.</p>";
        }
        break;

    // ----- Library -----
    case 'library':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by, t.photo
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='approved'
            ORDER BY t.date_submitted DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Tree Library</h2>";

        if(count($trees) == 0){
            echo "<p>No approved trees yet.</p>";
        } else {
            echo '<div class="tree-library">';
            foreach($trees as $t){
                $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
                echo "<div class='tree-card'>
                        <img src='{$photo}' alt='{$t['species_name']}'>
                        <h3>{$t['species_name']}</h3>
                        <p><strong>Location:</strong> {$t['location_name']}</p>
                        <p><strong>Submitted by:</strong> {$t['submitted_by']}</p>
                      </div>";
            }
            echo "</div>";
        }
        break;

    default:
        echo "<p>Tab not found.</p>";
}
?>
</div>
</body>
</html>