<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Ensure user is a student
if(!isRole('student')){
    die("Access Denied");
}
$user_id = $_SESSION['user_id'];
if(isset($_POST['submit_tree'])){

    $species_id = $_POST['species_id'];
    $location_name = $_POST['location_name'];

    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];

    $upload_path = "../uploads/" . $photo;

    move_uploaded_file($tmp, $upload_path);

    $stmt = $conn->prepare("
        INSERT INTO TREE_SUBMISSIONS
        (species_id, submitted_by, location_name, photo, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $species_id,
        $user_id,
        $location_name,
        $photo
    ]);

    echo "<p style='color:green;'>Tree submitted successfully!</p>";
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student'; 

// Determine current tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html>
<head>
    <title>TreeKnown - Student Dashboard</title>
    <style>
        body { font-family: Arial; margin:0; background:#f4f4f4; }
        header { background:#228B22; color:white; padding:15px; text-align:center; }
        nav { background:#2E8B57; display:flex; justify-content:center; flex-wrap:wrap; }
        nav a { color:white; text-decoration:none; margin:0 10px; padding:10px; }
        nav a.active { background:#3CB371; border-radius:5px; }
        nav a:hover { background:#3CB371; border-radius:5px; }
        .container { padding:20px; }
        .card { background:white; padding:15px; margin:10px 0; border-radius:5px; }
        ul { list-style:none; padding:0; }
        li { background:white; padding:10px; margin:5px 0; border-radius:5px; box-shadow:0 2px 3px rgba(0,0,0,0.1);}
    </style>
</head>
<body>
<header>
    <h1>TreeKnown - Student Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($user_name) ?></p>
</header>

<nav>
    <a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="?tab=submit" class="<?= $tab=='submit'?'active':'' ?>">Submit Tree</a>
    <a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
    <a href="?tab=scan" class="<?= $tab=='scan'?'active':'' ?>">Scan Tree</a>
    <a href="../logout.php" style="color:red; margin-left:20px;">Logout</a>
</nav>

<div class="container">
<?php
switch($tab){
    case 'dashboard':
        // Stats
        $total_submissions_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=?");
        $total_submissions_stmt->execute([$user_id]);
        $total_submissions = $total_submissions_stmt->fetchColumn();

        $approved_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='approved'");
        $approved_stmt->execute([$user_id]);
        $approved_submissions = $approved_stmt->fetchColumn();

        $pending_stmt = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='pending'");
        $pending_stmt->execute([$user_id]);
        $pending_submissions = $pending_stmt->fetchColumn();

        echo "<h2>Your Stats</h2>";
        echo "<div class='card'><strong>Total Submissions:</strong> $total_submissions</div>";
        echo "<div class='card'><strong>Approved:</strong> $approved_submissions</div>";
        echo "<div class='card'><strong>Pending:</strong> $pending_submissions</div>";
        break;

   case 'submit':
    echo "<h2>Submit a Tree</h2>";

    echo '<form method="post" enctype="multipart/form-data">';

    echo 'Species ID: <input type="number" name="species_id" required><br><br>';

    echo 'Location Name: <input type="text" name="location_name" required><br><br>';

    echo 'Photo: <input type="file" name="photo" required><br><br>';

    echo '<button type="submit" name="submit_tree">Submit Tree</button>';

    echo '</form>';

break;

    case 'library':
        // Approved/All student trees
        echo "<h2>Your Submitted Trees</h2>";
        $trees = $conn->prepare("
            SELECT t.tree_id, s.species_name, t.location_name, t.status, t.photo
            FROM TREE_SUBMISSIONS t
            JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
            WHERE t.submitted_by=?
            ORDER BY t.date_submitted DESC
        ");
        $trees->execute([$user_id]);
        $results = $trees->fetchAll(PDO::FETCH_ASSOC);

        if(count($results) > 0){
            echo "<ul>";
            foreach($results as $t){
                $status_class = $t['status']=='approved' ? 'status-approved' : ($t['status']=='pending' ? 'status-pending' : 'status-rejected');
                echo "<li>";
                echo "Tree ID {$t['tree_id']} - {$t['species_name']} at {$t['location_name']} ";
                echo "(Status: <span class='$status_class'>{$t['status']}</span>)<br>";
                if($t['photo']) echo "<img src='../uploads/{$t['photo']}' width='150'><br>";
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No submissions yet.</p>";
        }
        break;

    case 'scan':
        // Placeholder for AI scanning
        echo "<h2>Scan Tree (Coming Soon! AI MODEL STILL IN DEVELOPMENT)</h2>";
        echo "<p>This feature will allow you to scan a tree and automatically identify its species using AI.</p>";
        echo "<p>For now, please upload a photo in the Submit Tree tab.</p>";
        echo "<div style='background:#fff; padding:20px; border-radius:5px; text-align:center; box-shadow:0 2px 3px rgba(0,0,0,0.1)'>";
        echo "<img src='../assets/placeholder.png' alt='AI Scan Placeholder' width='300'>";
        echo "</div>";
        break;

    default:
        echo "<p>Tab not found</p>";
}
?>
</div>
</body>
</html>