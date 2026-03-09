<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Ensure user is a student
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student'){
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

if(isset($_POST['submit_tree'])){
    $species_id = $_POST['species_id'];
    $location_name = $_POST['location_name']; // descriptive name
    $lat = $_POST['lat'];  // precise latitude
    $lng = $_POST['lng'];  // precise longitude

    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $upload_path = "../uploads/" . $photo;
    move_uploaded_file($tmp, $upload_path);

  $stmt = $conn->prepare("
    INSERT INTO TREE_SUBMISSIONS 
    (species_id, submitted_by, location_name, lat, lng, photo, status)
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([$species_id, $user_id, $location_name, $lat, $lng, $photo]);
    $success = "Tree submitted successfully!";
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TreeKnown - Student </title>
<style>
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
        justify-content: center;
        flex-wrap: wrap;
        padding: 10px 0;
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
    .card {
        background: #fff;
        padding: 15px;
        margin: 10px 0;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }
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
    form input, form select, form button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    form button {
        background: #36A2EB;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
    }
    form button:hover {
        background: #2E8B57;
    }
    .success { color: green; margin-bottom: 15px; }
</style>
</head>
<body>
<header>
    <h1>TreeKnown - Student </h1>
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
    $total_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=?");
    $total_submissions->execute([$user_id]);
    $total_submissions = $total_submissions->fetchColumn();

    $approved_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='approved'");
    $approved_submissions->execute([$user_id]);
    $approved_submissions = $approved_submissions->fetchColumn();

    $pending_submissions = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='pending'");
    $pending_submissions->execute([$user_id]);
    $pending_submissions = $pending_submissions->fetchColumn();

    echo "<h2>Your Stats</h2>";
    echo "<div class='card'><strong>Total Submissions:</strong> $total_submissions</div>";
    echo "<div class='card'><strong>Approved:</strong> $approved_submissions</div>";
    echo "<div class='card'><strong>Pending:</strong> $pending_submissions</div>";
    break;

case 'submit':
    echo "<h2>Submit a Tree</h2>";
    if(isset($success)) echo "<p class='success'>$success</p>";

    // Fetch species for dropdown
    $species_stmt = $conn->query("SELECT species_id, species_name FROM SPECIES_LIBRARY ORDER BY species_name");
    $species_list = $species_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<form method="post" enctype="multipart/form-data">';
    
    // Species dropdown
    echo '<select name="species_id" required>
            <option value="">-- Select Species --</option>';
    foreach($species_list as $s){
        echo '<option value="'.$s['species_id'].'">'.htmlspecialchars($s['species_name']).'</option>';
    }
    echo '</select>';

    // Descriptive location name
    echo '<input type="text" name="location_name" placeholder="Location Name" required>';

    // Leaflet map for selecting coordinates
    echo '<p>Select exact location on map:</p>';
    echo '<div id="map" style="height:300px; margin-bottom:10px;"></div>';
    echo '<input type="hidden" name="lat" id="lat" required>';
    echo '<input type="hidden" name="lng" id="lng" required>';

    // Photo upload
    echo '<input type="file" name="photo" required>';
    echo '<button type="submit" name="submit_tree">Submit Tree</button>';
    echo '</form>';

    // Leaflet CSS & JS
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css"/>';
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>';

    // Leaflet initialization & click handler
    echo <<<EOT
<script>
const map = L.map('map').setView([8.360288, 124.868472], 20); 
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let marker;
map.on('click', function(e){
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    if(marker){
        marker.setLatLng(e.latlng);
    } else {
        marker = L.marker(e.latlng).addTo(map);
    }

    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
});
</script>
EOT;
break;

case 'library':
    $trees = $conn->query("
        SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by, t.photo, t.lat, t.lng
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
        // Hover map container
        echo '<div id="map-hover" style="width:300px; height:200px; position:fixed; top:100px; right:20px; border:1px solid #ccc; z-index:1000;"></div>';

        echo "<div class='tree-library'>";
        foreach($trees as $t){
            $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
            $lat = $t['lat'] ?? 0;
            $lng = $t['lng'] ?? 0;

            echo "<div class='tree-card' data-lat='{$lat}' data-lng='{$lng}'>
                    <img src='{$photo}' alt='{$t['species_name']}'>
                    <h3>{$t['species_name']}</h3>
                    <p><strong>Tree ID:</strong> {$t['tree_id']}</p>
                    <p><strong>Location:</strong> {$t['location_name']}</p>
                    <p><strong>Submitted by:</strong> {$t['submitted_by']}</p>
                  </div>";
        }
        echo "</div>";

        // Leaflet JS & CSS
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css"/>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>';

        // Hover map script
        echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hoverMap = L.map('map-hover', { zoomControl: false }).setView([0,0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(hoverMap);

    let hoverMarker;

    document.querySelectorAll('.tree-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);

            if(!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0){
                hoverMap.setView([lat, lng], 20);

                if(hoverMarker){
                    hoverMarker.setLatLng([lat, lng]);
                } else {
                    hoverMarker = L.marker([lat, lng]).addTo(hoverMap);
                }
            }
        });

        card.addEventListener('mouseleave', () => {
            hoverMap.setView([0,0], 2);
            if(hoverMarker) hoverMarker.remove();
            hoverMarker = null;
        });
    });
});
</script>
EOT;
    }
break;

case 'scan':
    echo "<h2>Scan Tree (Coming Soon!)</h2>";
    echo "<p>This feature will allow you to scan a tree and automatically identify its species using AI.</p>";
    echo "<div class='card' style='text-align:center;'>";
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