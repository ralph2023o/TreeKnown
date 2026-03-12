<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Only admin can access
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'){
    die("Access Denied");
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

/* USER APPROVAL */
if(isset($_GET['action'], $_GET['id']) && $tab == 'pending_users'){
    $id = intval($_GET['id']);
    if($_GET['action'] == 'approve'){
        $conn->prepare("UPDATE USERS SET status='approved' WHERE user_id=?")->execute([$id]);
    } elseif($_GET['action'] == 'reject'){
        $conn->prepare("UPDATE USERS SET status='rejected' WHERE user_id=?")->execute([$id]);
    }
    header("Location:?tab=pending_users");
    exit;
}

/* DELETE USER */
if(isset($_GET['action'], $_GET['id']) && $_GET['action']=='delete' && $tab=='users'){
    $id = intval($_GET['id']);
    if($id != $_SESSION['user_id']){
        $conn->prepare("DELETE FROM USERS WHERE user_id=?")->execute([$id]);
    }
    header("Location:?tab=users");
    exit();
}

/* EDIT TREE */
if(isset($_GET['action'], $_GET['id']) && $_GET['action']=='edit_tree'){
    $tree_id = intval($_GET['id']);
    if($_SERVER['REQUEST_METHOD']=="POST"){
        $location = $_POST['location_name'];
        $species_id = $_POST['species_id'];
        $conn->prepare("UPDATE TREE_SUBMISSIONS SET location_name=?, species_id=? WHERE tree_id=?")
            ->execute([$location,$species_id,$tree_id]);
        header("Location:?tab=library");
        exit;
    }
    $tree = $conn->prepare("SELECT * FROM TREE_SUBMISSIONS WHERE tree_id=?");
    $tree->execute([$tree_id]);
    $tree = $tree->fetch(PDO::FETCH_ASSOC);
    $species_list = $conn->query("SELECT * FROM TREE_LIBRARY")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>TreeKnown Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
body{display:flex;background:#f4f6f9;}
.sidebar{width:220px;height:100vh;background:#1f4d2b;color:white;position:fixed;}
.sidebar h2{text-align:center;padding:20px;background:#163a20;}
.sidebar a{display:block;padding:15px;color:white;text-decoration:none;border-bottom:1px solid rgba(255,255,255,0.1);}
.sidebar a:hover{background:#2e8b57;}
.sidebar a.active{background:#3cb371;}
.main{margin-left:220px;padding:25px;width:100%;}
header{background:white;padding:15px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);margin-bottom:20px;}
.cards{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:25px;}
.card{flex:1;min-width:200px;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.card h3{color:#666;}
.card p{font-size:28px;font-weight:bold;color:#2e8b57;}
table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
th{background:#2e8b57;color:white;padding:12px;}
td{padding:10px;border-bottom:1px solid #eee;}
tr:hover{background:#f9f9f9;}
.btn{padding:6px 12px;border-radius:5px;color:white;text-decoration:none;font-size:13px;}
.approve{background:#28a745;}
.reject{background:#dc3545;}
.edit{background:#36A2EB;}
.delete{background:#ff4444;}
.tree-grid{display:flex;flex-wrap:wrap;gap:20px;}
.tree-card{width:220px;background:white;border-radius:10px;padding:15px;box-shadow:0 2px 6px rgba(0,0,0,0.1);text-align:center;}
.tree-card img{width:150px;height:150px;object-fit:cover;border-radius:5px;margin-bottom:10px;}
form input, form select{width:100%;padding:12px;margin:10px 0;border-radius:10px;border:1px solid #ccc;}
form button{background:#22c55e;color:white;padding:12px;border:none;border-radius:10px;cursor:pointer;}
</style>
</head>

<body>
<div class="sidebar">
<h2>🌳 TreeKnown</h2>
<a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
<a href="?tab=pending_users" class="<?= $tab=='pending_users'?'active':'' ?>">User Approval</a>
<a href="?tab=users" class="<?= $tab=='users'?'active':'' ?>">Users</a>
<a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
<a href="../logout.php" style="color:#ff8080;">Logout</a>
</div>

<div class="main">
<header><h1>Admin Dashboard</h1></header>

<?php
switch($tab){

/* DASHBOARD */
case 'dashboard':
    $total_students = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='student'")->fetchColumn();
    $total_teachers = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='teacher'")->fetchColumn();
    $total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();
    $pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
    $approved_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='approved'")->fetchColumn();
    $rejected_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='rejected'")->fetchColumn();

    echo "<div class='cards'>
            <div class='card'><h3>Students</h3><p>$total_students</p></div>
            <div class='card'><h3>Teachers</h3><p>$total_teachers</p></div>
            <div class='card'><h3>Total Trees</h3><p>$total_trees</p></div>
            <div class='card'><h3>Pending Trees</h3><p>$pending_trees</p></div>
          </div>";

    echo "<div style='display:flex;gap:50px;flex-wrap:wrap;'>
            <div style='width:400px'><canvas id='usersChart'></canvas></div>
            <div style='width:400px'><canvas id='treesChart'></canvas></div>
          </div>";

    echo "<script>
        new Chart(document.getElementById('usersChart'),{
            type:'doughnut',
            data:{labels:['Students','Teachers'],datasets:[{data:[$total_students,$total_teachers],backgroundColor:['#36A2EB','#FF6384']}]}
        });
        new Chart(document.getElementById('treesChart'),{
            type:'doughnut',
            data:{labels:['Pending','Approved','Rejected'],datasets:[{data:[$pending_trees,$approved_trees,$rejected_trees],backgroundColor:['#FFCE56','#36A2EB','#FF6384']}]}
        });
    </script>";
break;

/* USER APPROVAL */
case 'pending_users':
    $users = $conn->query("SELECT * FROM USERS WHERE status='pending'")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>Pending Users</h2>";
    if(count($users)==0){ echo "<p>No pending users.</p>"; }
    else{
        echo "<table>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Student ID</th><th>Actions</th></tr>";
        foreach($users as $u){
            echo "<tr>
                    <td>{$u['user_id']}</td>
                    <td>{$u['name']}</td>
                    <td>{$u['email']}</td>
                    <td>{$u['role']}</td>
                    <td>{$u['student_id']}</td>
                    <td>
                        <a class='btn approve' href='?tab=pending_users&action=approve&id={$u['user_id']}'>Approve</a>
                        <a class='btn reject' href='?tab=pending_users&action=reject&id={$u['user_id']}'>Reject</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    }
break;

/* USERS */
case 'users':
    $users = $conn->query("SELECT * FROM USERS")->fetchAll(PDO::FETCH_ASSOC);
    echo "<h2>User Management</h2>";
    echo "<table><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>";
    foreach($users as $u){
        echo "<tr>
                <td>{$u['name']}</td>
                <td>{$u['email']}</td>
                <td>{$u['role']}</td>
                <td>
                    <a class='btn edit' href='?tab=users&action=edit&id={$u['user_id']}'>Edit</a>
                    <a class='btn delete' onclick=\"return confirm('Delete this user?')\" href='?tab=users&action=delete&id={$u['user_id']}'>Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";
break;

/* TREE LIBRARY */
case 'library':
    // If editing a tree
    if(isset($_GET['action']) && $_GET['action']=='edit_tree' && isset($tree)){
        echo "<h2>Edit Tree</h2>";
        echo "<form method='POST'>";
        echo "Location:<input type='text' name='location_name' value='{$tree['location_name']}' required><br><br>";
        echo "Species:<select name='species_id'>";
        foreach($species_list as $s){
            $selected = ($s['treelib_id'] == $tree['species_id']) ? 'selected' : '';
            echo "<option value='{$s['treelib_id']}' $selected>{$s['tree_name']}</option>";
        }
        echo "</select><br><br>";
        echo "<button type='submit'>Update Tree</button>";
        echo "</form>";
    } else {
        $trees = $conn->query("
            SELECT t.tree_id, t.location_name, t.photo, u.name AS submitted_by, s.tree_name
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by=u.user_id
            LEFT JOIN TREE_LIBRARY s ON t.species_id=s.treelib_id
            WHERE t.status='approved'
            ORDER BY t.date_submitted DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Tree Library</h2>";
        if(count($trees)==0){ echo "<p>No approved trees yet.</p>"; }
        else{
            echo "<div class='tree-grid'>";
            foreach($trees as $t){
                $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
                $tree_name = $t['tree_name'] ?? 'Unknown';
                echo "<div class='tree-card'>
                        <img src='$photo' alt='$tree_name'>
                        <h3>$tree_name</h3>
                        <p>{$t['location_name']}</p>
                        <small>Submitted by {$t['submitted_by']}</small>
                        <br><br>
                        <a class='btn edit' href='?tab=library&action=edit_tree&id={$t['tree_id']}'>Edit</a>
                      </div>";
            }
            echo "</div>";
        }
    }
break;
}
?>
</div>
</body>
</html>