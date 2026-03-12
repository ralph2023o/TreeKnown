```php
<?php 
session_start();
include __DIR__ . '/../config/db.php'; 

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student'){
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

if(isset($_POST['submit_tree'])){
    $species_id = $_POST['species_id'];
    $location_name = $_POST['location_name'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $upload_path = "../uploads/" . $photo;

    move_uploaded_file($tmp,$upload_path);

    $stmt = $conn->prepare("
        INSERT INTO TREE_SUBMISSIONS 
        (species_id, submitted_by, location_name, lat, lng, photo, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([$species_id,$user_id,$location_name,$lat,$lng,$photo]);

    $success = "🌳 Tree submitted successfully!";
}

$tab = $_GET['tab'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>TreeKnown Student</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Segoe UI;
}

body{
background:linear-gradient(135deg,#d4fc79,#96e6a1);
display:flex;
min-height:100vh;
}

/* SIDEBAR */

.sidebar{
width:230px;
height:100vh;
background:linear-gradient(180deg,#14532d,#16a34a);
padding:25px;
color:white;
position:fixed;
}

.sidebar h2{
text-align:center;
margin-bottom:30px;
}

.sidebar a{
display:block;
padding:12px;
margin:8px 0;
color:white;
text-decoration:none;
border-radius:8px;
transition:all 0.3s ease;
}

.sidebar a:hover{
background:rgba(255,255,255,0.25);
transform:translateX(5px);
}

.sidebar a.active{
background:rgba(255,255,255,0.25);
}

/* MAIN */

.main{
margin-left:230px;
padding:30px;
width:100%;
}

/* HEADER */

header{
background:white;
padding:20px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,0.1);
margin-bottom:25px;
}

/* DASHBOARD */

.stats{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;
}

.stat-card{
background:white;
padding:25px;
border-radius:15px;
text-align:center;
box-shadow:0 10px 20px rgba(0,0,0,0.1);
transition:all 0.3s ease;
}

.stat-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 30px rgba(0,0,0,0.2);
}

/* FORM CENTER */

.form-center{
display:flex;
justify-content:center;
}

/* FORM */

form{
background:white;
padding:30px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,0.15);
max-width:500px;
width:100%;
}

form input,
form select{
width:100%;
padding:12px;
margin:10px 0;
border-radius:10px;
border:1px solid #ccc;
}

form button{
background:#22c55e;
color:white;
padding:12px;
border:none;
border-radius:10px;
cursor:pointer;
transition:all 0.3s ease;
}

form button:hover{
background:#16a34a;
transform:scale(1.05);
}

/* MAP */

#map{
height:300px;
border-radius:10px;
margin-bottom:10px;
}

/* SUCCESS MESSAGE */

.success{
color:green;
font-weight:bold;
margin-bottom:10px;
}

/* TREE LIBRARY */

.tree-library{
display:grid;
grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
gap:20px;
margin-top:20px;
}

.tree-card{
background:white;
border-radius:15px;
padding:15px;
text-align:center;
box-shadow:0 5px 15px rgba(0,0,0,0.1);
transition:all 0.3s ease;
}

.tree-card:hover{
transform:translateY(-8px) scale(1.03);
box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.tree-card img{
width:100%;
height:160px;
object-fit:cover;
border-radius:10px;
margin-bottom:10px;
}

</style>

</head>

<body>

<div class="sidebar">

<h2>🌳 TreeKnown</h2>

<a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
<a href="?tab=submit" class="<?= $tab=='submit'?'active':'' ?>">Submit Tree</a>
<a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
<a href="?tab=scan" class="<?= $tab=='scan'?'active':'' ?>">Scan Tree</a>
<a href="../logout.php" style="background:red;">Logout</a>

</div>

<div class="main">

<header>
<h1>Welcome, <?= htmlspecialchars($user_name) ?> 🌿</h1>
</header>

<?php

switch($tab){

/* DASHBOARD */

case 'dashboard':

$total = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=?");
$total->execute([$user_id]);
$total = $total->fetchColumn();

$approved = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='approved'");
$approved->execute([$user_id]);
$approved = $approved->fetchColumn();

$pending = $conn->prepare("SELECT COUNT(*) FROM TREE_SUBMISSIONS WHERE submitted_by=? AND status='pending'");
$pending->execute([$user_id]);
$pending = $pending->fetchColumn();

echo "<h2>Student Dashboard</h2>";

echo "<div class='stats'>

<div class='stat-card'>
<h3>Total Trees</h3>
<p>$total</p>
</div>

<div class='stat-card'>
<h3>Approved</h3>
<p>$approved</p>
</div>

<div class='stat-card'>
<h3>Pending</h3>
<p>$pending</p>
</div>

</div>";

break;


/* SUBMIT TREE */

case 'submit':

echo "<h2 style='text-align:center;'>Submit a Tree</h2>";

echo "<div class='form-center'>";

if(isset($success)) echo "<p class='success'>$success</p>";

$species = $conn->query("SELECT species_id,species_name FROM SPECIES_LIBRARY ORDER BY species_name");

echo '<form method="post" enctype="multipart/form-data">';

echo '<select name="species_id" required>
<option value="">Select Tree Species</option>';

foreach($species as $s){
echo "<option value='{$s['species_id']}'>{$s['species_name']}</option>";
}

echo '</select>';

echo '<input type="text" name="location_name" placeholder="Location Name" required>';

echo '<p>Click the map to mark the tree location</p>';

echo '<div id="map"></div>';

echo '<input type="hidden" name="lat" id="lat" required>';
echo '<input type="hidden" name="lng" id="lng" required>';

echo '<input type="file" name="photo" required>';

echo '<button type="submit" name="submit_tree">Submit Tree</button>';

echo '</form>';

echo "</div>";
?>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

const map=L.map('map').setView([8.360288,124.868472],19);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
attribution:'© OpenStreetMap'
}).addTo(map);

let marker;

map.on('click',function(e){

const lat=e.latlng.lat;
const lng=e.latlng.lng;

if(marker){
marker.setLatLng(e.latlng);
}else{
marker=L.marker(e.latlng).addTo(map);
}

document.getElementById('lat').value=lat;
document.getElementById('lng').value=lng;

});

</script>

<?php
break;


/* TREE LIBRARY */

case 'library':

$trees=$conn->query("
SELECT t.tree_id,s.species_name,t.location_name,u.name AS submitted_by,t.photo
FROM TREE_SUBMISSIONS t
JOIN USERS u ON t.submitted_by=u.user_id
JOIN SPECIES_LIBRARY s ON t.species_id=s.species_id
WHERE t.status='approved'
")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Tree Library</h2>";

echo "<div class='tree-library'>";

foreach($trees as $t){

$photo=$t['photo'] ? "../uploads/".$t['photo'] : "https://via.placeholder.com/200";

echo "<div class='tree-card'>

<img src='$photo'>

<h3>{$t['species_name']}</h3>

<p>{$t['location_name']}</p>

<p>By {$t['submitted_by']}</p>

</div>";
}

echo "</div>";

break;


/* SCAN */

case 'scan':

echo "<h2>🌳 AI Tree Scanner (Coming Soon)</h2>";
echo "<p>This will allow scanning trees using camera.</p>";

break;

}

?>

</div>

</body>
</html>
```
