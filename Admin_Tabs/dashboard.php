<?php
session_start();
include __DIR__ . '/../config/db.php'; 

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'){
    die("Access Denied");
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Handle user approval/reject
if(isset($_GET['action'], $_GET['id']) && $tab == 'pending_users'){
    $id = intval($_GET['id']);
    if($_GET['action'] == 'approve'){
        $conn->prepare("UPDATE USERS SET status='approved' WHERE user_id=?")->execute([$id]);
    } elseif($_GET['action'] == 'reject'){
        $conn->prepare("UPDATE USERS SET status='rejected' WHERE user_id=?")->execute([$id]);
    }
    header("Location: ?tab=pending_users");
    exit;
}
if(isset($_GET['action'], $_GET['id']) && $_GET['action']=='delete' && $tab=='users'){
    $id = intval($_GET['id']);
    // Prevent deleting yourself
    if($id != $_SESSION['user_id']){
        $conn->prepare("DELETE FROM USERS WHERE user_id=?")->execute([$id]);
    }
    header("Location: ?tab=users");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - ADMIN </title>
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
        table { width:100%; border-collapse: collapse; margin-top: 15px;}
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left;}
        th { background: #36A2EB; color: #fff;}
        a.action-btn { padding:5px 10px; text-decoration:none; border-radius:5px; color:#fff; margin-right:5px; }
        a.approve { background:green; }
        a.reject { background:red; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <h1>TreeKnown - Admin </h1>
</header>

<nav style="display:flex; align-items:center; background:#2E8B57;">
    <div style="display:flex; flex:1;">
        <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="?tab=pending_users" class="<?= $tab=='pending_users'?'active':'' ?>">User Approval</a>
        <a href="?tab=users" class="<?= $tab=='users'?'active':'' ?>">Users</a>
        <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
    </div>
    <a href="../logout.php" style="color:red;">Logout</a>
</nav>

<div class="container">
<?php
switch($tab){

    case 'dashboard':
        $total_students = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='student'")->fetchColumn();
        $total_teachers = $conn->query("SELECT COUNT(*) FROM USERS WHERE role='teacher'")->fetchColumn();
        $total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();
        $pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
        $approved_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='approved'")->fetchColumn();
        $rejected_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='rejected'")->fetchColumn();

        echo "<h2>Dashboard</h2>";
        echo "
        <div style='display:flex; gap:50px; flex-wrap:wrap;'>
            <div style='width:400px;'>
                <h3>Users</h3>
                <canvas id='usersChart'></canvas>
            </div>
            <div style='width:400px;'>
                <h3>Trees</h3>
                <canvas id='treesChart'></canvas>
            </div>
        </div>
        <script>
        const ctxUsers = document.getElementById('usersChart').getContext('2d');
        new Chart(ctxUsers,{type:'doughnut',data:{labels:['Students','Teachers'],datasets:[{label:'Users',data:[$total_students,$total_teachers],backgroundColor:['#36A2EB','#FF6384']}]},options:{responsive:true}});
        
        const ctxTrees = document.getElementById('treesChart').getContext('2d');
        new Chart(ctxTrees,{type:'doughnut',data:{labels:['Pending','Approved','Rejected'],datasets:[{label:'Trees',data:[$pending_trees,$approved_trees,$rejected_trees],backgroundColor:['#FFCE56','#36A2EB','#FF6384']}]},options:{responsive:true}});
        </script>";
        break;

    case 'pending_users':
        $users = $conn->query("SELECT * FROM USERS WHERE status='pending' ORDER BY user_id DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo "<h2>Pending User Approvals</h2>";
        if(count($users) == 0){
            echo "<p>No pending users.</p>";
        } else {
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Student ID</th>
                        <th>Actions</th>
                    </tr>";
            foreach($users as $u){
                echo "<tr>
                        <td>{$u['user_id']}</td>
                        <td>{$u['name']}</td>
                        <td>{$u['email']}</td>
                        <td>{$u['role']}</td>
                        <td>{$u['student_id']}</td>
                        <td>
                            <a href='?tab=pending_users&action=approve&id={$u['user_id']}' class='action-btn approve'>Approve</a>
                            <a href='?tab=pending_users&action=reject&id={$u['user_id']}' class='action-btn reject'>Reject</a>
                        </td>
                     </tr>";
            }
            echo "</table>";
        }
        break;

    case 'users':
        if(isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM USERS WHERE user_id=?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$user){
                header("Location: ?tab=users");
                exit();
            }

            echo "
            <h2>Edit User</h2>
            <form method='POST'>
                Name:<br>
                <input type='text' name='name' value='{$user['name']}' required><br><br>
                Email:<br>
                <input type='email' name='email' value='{$user['email']}' required><br><br>
                Role:<br>
                <select name='role'>
                    <option value='admin' ".($user['role']=='admin'?'selected':'').">Admin</option>
                    <option value='teacher' ".($user['role']=='teacher'?'selected':'').">Teacher</option>
                    <option value='student' ".($user['role']=='student'?'selected':'').">Student</option>
                </select><br><br>
                <button type='submit'>Update User</button>
            </form>";
        } else {
            // List users
            $users = $conn->query("SELECT * FROM USERS")->fetchAll(PDO::FETCH_ASSOC);
            echo "<h2>User Management</h2><ul>";
            foreach($users as $u){
         echo "<li>
            {$u['name']} ({$u['role']}) - {$u['email']} 
            [<a href='?tab=users&action=edit&id={$u['user_id']}'>Edit</a>] 
            [<a href='?tab=users&action=delete&id={$u['user_id']}' onclick=\"return confirm('Are you sure you want to delete this user?');\" style='color:red;'>Delete</a>]
          </li>";
}
            echo "</ul>";
        }
        break;

    case 'library':
        $trees = $conn->query("
            SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by, t.photo
            FROM TREE_SUBMISSIONS t
            JOIN USERS u ON t.submitted_by = u.user_id
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.status='approved'
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Tree Library (Approved)</h2>";
        echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";

        foreach($trees as $t){
            $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
            echo "
            <div style='border:1px solid #ccc; border-radius:10px; padding:15px; width:220px; box-shadow:0 2px 5px rgba(0,0,0,0.2); display:flex; flex-direction:column; align-items:center; background:#fff;'>
                <img src='{$photo}' style='width:150px; height:150px; object-fit:cover; border-radius:5px; margin-bottom:10px;'>
                <h3 style='margin:5px 0;'>{$t['species_name']}</h3>
                <p style='margin:2px 0; font-size:14px; color:#555;'>{$t['location_name']}</p>
                <p style='margin:2px 0; font-size:12px; color:#777;'>Submitted by {$t['submitted_by']}</p>
                <a href='?tab=edit_tree&id={$t['tree_id']}' style='margin-top:10px; padding:5px 10px; background:#36A2EB; color:#fff; text-decoration:none; border-radius:5px;'>Edit</a>
            </div>";
        }
        echo "</div>";
        break;

    case 'edit_tree':
        if(!isset($_GET['id'])){
            echo "<p>No tree selected.</p>";
            break;
        }

        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT tree_id, location_name, species_id FROM TREE_SUBMISSIONS WHERE tree_id=?");
        $stmt->execute([$id]);
        $tree = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$tree){
            echo "<p>Tree not found.</p>";
            break;
        }

        $species = $conn->query("SELECT * FROM SPECIES_LIBRARY")->fetchAll(PDO::FETCH_ASSOC);

        // Save update
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $location = $_POST['location_name'];
            $species_id = $_POST['species_id'];
            $update = $conn->prepare("UPDATE TREE_SUBMISSIONS SET location_name=?, species_id=? WHERE tree_id=?");
            $update->execute([$location, $species_id, $id]);
            header("Location: ?tab=library");
            exit();
        }

        // Display form
        echo "
        <h2>Edit Tree Entry</h2>
        <form method='POST'>
            Location Name:<br>
            <input type='text' name='location_name' value='{$tree['location_name']}' required><br><br>
            Species:<br>
            <select name='species_id'>";
            foreach($species as $s){
                $selected = ($s['species_id'] == $tree['species_id']) ? "selected" : "";
                echo "<option value='{$s['species_id']}' $selected>{$s['species_name']}</option>";
            }
        echo "</select><br><br>
            <button type='submit'>Update Tree</button>
        </form>";
        break;

    default:
        echo "<p>Tab not found</p>";
}


?>
</div>
</body>
</html>