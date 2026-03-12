    <?php
    session_start();
    include __DIR__ . '/../config/db.php'; 

    // Only teachers can access
    if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'teacher'){
        die("Access Denied");
    }

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

    // Handle Approve/Reject actions
    if(isset($_GET['action'], $_GET['id']) && $tab == 'verification'){
        $id = intval($_GET['id']);
        if($_GET['action'] == 'approve'){
            $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='approved' WHERE tree_id=?")->execute([$id]);
        }
        if($_GET['action'] == 'reject'){
            $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='rejected' WHERE tree_id=?")->execute([$id]);
        }
        header("Location:?tab=verification");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TreeKnown - Teacher Dashboard</title>
    <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: Arial; }
    body { display:flex; background:#f4f6f9; }

    /* SIDEBAR */
    .sidebar{
        width:220px;
        height:100vh;
        background:#1f4d2b;
        color:white;
        position:fixed;
    }
    .sidebar h2{
        text-align:center;
        padding:20px;
        background:#163a20;
    }
    .sidebar a{
        display:block;
        padding:15px;
        color:white;
        text-decoration:none;
        border-bottom:1px solid rgba(255,255,255,0.1);
    }
    .sidebar a:hover, .sidebar a.active{
        background:#3cb371;
    }

    /* MAIN */
    .main{
        margin-left:220px;
        padding:25px;
        width:100%;
    }
    header{
        background:white;
        padding:15px;
        border-radius:10px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
        margin-bottom:20px;
    }

    /* CARDS */
    .cards{
        display:flex;
        gap:20px;
        flex-wrap:wrap;
        margin-bottom:25px;
    }
    .card{
        flex:1;
        min-width:200px;
        background:white;
        padding:20px;
        border-radius:10px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
        text-align:center;
    }
    .card h3{ color:#666; }
    .card p{ font-size:28px; font-weight:bold; color:#2e8b57; }

    /* TABLE */
    table{
        width:100%;
        border-collapse:collapse;
        background:white;
        border-radius:10px;
        overflow:hidden;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }
    th{
        background:#2e8b57;
        color:white;
        padding:12px;
    }
    td{
        padding:10px;
        border-bottom:1px solid #eee;
    }
    tr:hover{ background:#f9f9f9; }

    /* BUTTONS */
    .btn{
        padding:6px 12px;
        border-radius:5px;
        color:white;
        text-decoration:none;
        font-size:13px;
    }
    .approve{background:#28a745;}
    .reject{background:#dc3545;}

    /* TREE LIBRARY */
    .tree-grid{
        display:flex;
        flex-wrap:wrap;
        gap:20px;
    }
    .tree-card{
        width:220px;
        background:white;
        border-radius:10px;
        padding:15px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);
        text-align:center;
    }
    .tree-card img{
        width:150px;
        height:150px;
        object-fit:cover;
        border-radius:5px;
        margin-bottom:10px;
    }
    </style>
    </head>

    <body>
    <div class="sidebar">
    <h2>🌳 TreeKnown</h2>
    <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="?tab=verification" class="<?= $tab=='verification'?'active':'' ?>">Tree Verification</a>
    <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
    <a href="../logout.php" style="color:#ff8080;">Logout</a>
    </div>

    <div class="main">
    <header>
    <h1>Teacher Dashboard</h1>
    </header>

    <?php
    switch($tab){

        /* DASHBOARD */
        case 'dashboard':
            $total_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS")->fetchColumn();
            $pending_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='pending'")->fetchColumn();
            $approved_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='approved'")->fetchColumn();
            $rejected_trees = $conn->query("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE status='rejected'")->fetchColumn();

            echo "<div class='cards'>
                    <div class='card'><h3>Total Trees</h3><p>$total_trees</p></div>
                    <div class='card'><h3>Pending</h3><p>$pending_trees</p></div>
                    <div class='card'><h3>Approved</h3><p>$approved_trees</p></div>
                    <div class='card'><h3>Rejected</h3><p>$rejected_trees</p></div>
                </div>";
            break;

        /* TREE VERIFICATION */
        case 'verification':
            $trees = $conn->query("
                SELECT t.tree_id, t.species_guess, s.tree_name, t.location_name, u.name AS submitted_by, t.photo
                FROM TREE_SUBMISSIONS t
                JOIN USERS u ON t.submitted_by = u.user_id
                LEFT JOIN TREE_LIBRARY s ON t.species_id = s.treelib_id
                WHERE t.status='pending'
                ORDER BY t.date_submitted DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            echo "<h2>Pending Tree Verification</h2>";
            if(count($trees) > 0){
                echo "<table>
                        <tr><th>ID</th><th>Student Guess</th><th>Library Match</th><th>Location</th><th>Submitted By</th><th>Photo</th><th>Actions</th></tr>";
                foreach($trees as $t){
                    $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/100";
                    $library = $t['tree_name'] ?? 'N/A';
                    echo "<tr>
                            <td>{$t['tree_id']}</td>
                            <td>{$t['species_guess']}</td>
                            <td>$library</td>
                            <td>{$t['location_name']}</td>
                            <td>{$t['submitted_by']}</td>
                            <td><img src='$photo' width='80'></td>
                            <td>
                                <a class='btn approve' href='?tab=verification&action=approve&id={$t['tree_id']}'>Approve</a>
                                <a class='btn reject' href='?tab=verification&action=reject&id={$t['tree_id']}'>Reject</a>
                            </td>
                        </tr>";
                }
                echo "</table>";
            } else { echo "<p>No pending trees.</p>"; }
            break;

        /* TREE LIBRARY */
    case 'library':
      $trees = $conn->query("
    SELECT t.tree_id, s.tree_name, t.species_guess, t.location_name, u.name AS submitted_by, t.photo
    FROM TREE_SUBMISSIONS t
    JOIN USERS u ON t.submitted_by = u.user_id
    LEFT JOIN TREE_LIBRARY s ON t.species_id = s.treelib_id
    WHERE t.status='approved'
    ORDER BY t.date_submitted DESC
")->fetchAll(PDO::FETCH_ASSOC); 

        echo "<h2>Tree Library</h2>";
        if(count($trees) == 0){
            echo "<p>No approved trees yet.</p>";
        } else {
            echo "<div class='tree-grid'>";
           foreach($trees as $t){
    $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
    // Use library name if available, otherwise fallback to student's guess
    $tree_name = $t['tree_name'] ?? $t['species_guess'] ?? 'Unknown';

    echo "<div class='tree-card'>
            <img src='$photo' alt='$tree_name'>
            <h3>$tree_name</h3>
            <p>{$t['location_name']}</p>
            <small>Submitted by {$t['submitted_by']}</small>
          </div>";
}
            echo "</div>";
        }
        break;
    }
    ?>
    </div>
    </body>
    </html>