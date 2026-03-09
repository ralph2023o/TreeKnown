<?php
session_start();
include __DIR__ . '/../config/db.php'; 


if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin'){
    die("Access Denied");
}


$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - ADMIN Dashboard</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <h1>TreeKnown - Admin Dashboard</h1>
</header>

<nav style="display:flex; align-items:center; background:#2E8B57;">
    <div style="display:flex; flex:1;">
        <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
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

    // Canvas for charts
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
    // Users Chart
    const ctxUsers = document.getElementById('usersChart').getContext('2d');
    new Chart(ctxUsers, {
        type: 'doughnut',
        data: {
            labels: ['Students','Teachers'],
            datasets: [{
                label: 'Users',
                data: [$total_students, $total_teachers],
                backgroundColor: ['#36A2EB','#FF6384']
            }]
        },
        options: { responsive:true }
    });

    // Trees Chart
    const ctxTrees = document.getElementById('treesChart').getContext('2d');
    new Chart(ctxTrees, {
        type: 'doughnut',
        data: {
            labels: ['Pending','Approved','Rejected'],
            datasets: [{
                label: 'Trees',
                data: [$pending_trees,$approved_trees,$rejected_trees],
                backgroundColor: ['#FFCE56','#36A2EB','#FF6384']
            }]
        },
        options: { responsive:true }
    });
    </script>
    ";
    break;
    case 'users':
        // Handle edit
        if(isset($_GET['action']) && $_GET['action']=='edit' && isset($_GET['id'])){
            $id = $_GET['id'];

            $stmt = $conn->prepare("SELECT * FROM USERS WHERE user_id=?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                $name = $_POST['name'];
                $email = $_POST['email'];
                $role = $_POST['role'];

                $update = $conn->prepare("UPDATE USERS SET name=?, email=?, role=? WHERE user_id=?");
                $update->execute([$name,$email,$role,$id]);

                header("Location: ?tab=users");
                exit();
            }

            echo "<h2>Edit User</h2>
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
                echo "<li>{$u['name']} ({$u['role']}) - {$u['email']} 
                [<a href='?tab=users&action=edit&id={$u['user_id']}'>Edit</a>]
                </li>";
            }
            echo "</ul>";
        }
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
        SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by, t.photo
        FROM TREE_SUBMISSIONS t
        JOIN USERS u ON t.submitted_by = u.user_id
        JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
        WHERE t.status='approved'
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Tree Library (Approved)</h2>";

    // Card container
    echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";

    foreach($trees as $t){
        $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
        echo "<div style='
            border:1px solid #ccc; 
            border-radius:10px; 
            padding:15px; 
            width:220px; 
            box-shadow:0 2px 5px rgba(0,0,0,0.2);
            display:flex;
            flex-direction:column;
            align-items:center;
            background:#fff;
        '>
            <img src='{$photo}' style='width:150px; height:150px; object-fit:cover; border-radius:5px; margin-bottom:10px;'>
            <h3 style='margin:5px 0;'>{$t['species_name']}</h3>
            <p style='margin:2px 0; font-size:14px; color:#555;'>{$t['location_name']}</p>
            <p style='margin:2px 0; font-size:12px; color:#777;'>Submitted by {$t['submitted_by']}</p>
            <a href='?tab=edit_tree&id={$t['tree_id']}' style='
                margin-top:10px; 
                padding:5px 10px; 
                background:#36A2EB; 
                color:#fff; 
                text-decoration:none; 
                border-radius:5px;
            '>Edit</a>
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

        // Fetch tree info
        $stmt = $conn->prepare("SELECT tree_id, location_name, species_id FROM TREE_SUBMISSIONS WHERE tree_id=?");
        $stmt->execute([$id]);
        $tree = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$tree){
            echo "<p>Tree not found.</p>";
            break;
        }

        // Fetch species list
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
        echo "<h2>Edit Tree Entry</h2>
        <form method='POST'>
            Location Name:<br>
            <input type='text' name='location_name' value='{$tree['location_name']}' required>
            <br><br>
            Species:<br>
            <select name='species_id'>";

        foreach($species as $s){
            $selected = ($s['species_id'] == $tree['species_id']) ? "selected" : "";
            echo "<option value='{$s['species_id']}' $selected>{$s['species_name']}</option>";
        }

        echo "</select>
            <br><br>
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