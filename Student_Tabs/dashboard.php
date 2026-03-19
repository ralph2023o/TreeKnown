<?php 
session_start();
include __DIR__ . '/../config/db.php'; 

if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'student'){
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

/* TREE SUBMISSION */
$success = false;
if(isset($_POST['submit_tree'])){
    $species_guess = $_POST['species_guess'];
    $location_name = $_POST['location_name'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $photo = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];
    $upload_path = "../uploads/".$photo;
    move_uploaded_file($tmp,$upload_path);

    $stmt = $conn->prepare("
        INSERT INTO TREE_SUBMISSIONS
        (species_guess,submitted_by,location_name,lat,lng,photo,status)
        VALUES (?,?,?,?,?,?,'pending')
    ");
    $stmt->execute([
        $species_guess,
        $user_id,
        $location_name,
        $lat,
        $lng,
        $photo
    ]);

    $success = true;
}

/* COMMENT SUBMISSION */
if(isset($_POST['submit_comment'])){
    $tree_id = $_POST['tree_id'];
    $comment = $_POST['comment_text'];
    $parent = $_POST['parent_comment_id'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO COMMENTS(tree_id,user_id,parent_comment_id,comment_text)
        VALUES(?,?,?,?)
    ");
    $stmt->execute([
        $tree_id,
        $user_id,
        $parent,
        $comment
    ]);
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
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{background:linear-gradient(135deg,#d4fc79,#96e6a1);display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:230px;height:100vh;background:linear-gradient(180deg,#14532d,#16a34a);padding:25px;color:white;position:fixed;}
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar a{display:block;padding:12px;margin:8px 0;color:white;text-decoration:none;border-radius:8px;transition:0.3s;}
.sidebar a:hover{background:rgba(255,255,255,0.25);transform:translateX(5px);}
.sidebar a.active{background:rgba(255,255,255,0.25);}

/* MAIN */
.main{margin-left:230px;padding:30px;width:100%;}

/* HEADER */
header{background:white;padding:20px;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.1);margin-bottom:25px;}

/* DASHBOARD */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.stat-card{background:white;padding:25px;border-radius:15px;text-align:center;box-shadow:0 10px 20px rgba(0,0,0,0.1);}

/* FORM */
form{background:white;padding:25px;border-radius:15px;box-shadow:0 10px 25px rgba(0,0,0,0.15);max-width:500px;}
form input, form textarea{width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #ccc;}
form button{background:#22c55e;color:white;padding:10px;border:none;border-radius:8px;cursor:pointer;}

/* MAP */
#map{height:300px;border-radius:10px;margin-bottom:10px;}

/* TREE GRID */
.tree-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.tree-card{
    background:white;
    border-radius:15px;
    padding:15px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    max-width:250px;   /* LIMIT CARD WIDTH */
    margin:auto;        /* CENTER CARD */
}
.tree-card img{
    width:100%;
    height:160px;
    object-fit:cover;
    border-radius:10px;
}

/* TREE DETAIL */
.tree-detail{display:flex;gap:20px;background:white;padding:20px;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,0.1);align-items:center;margin-bottom:20px;}
.tree-detail img{width:220px;height:160px;object-fit:cover;border-radius:10px;}

/* COMMENTS */
.comment{background:white;padding:12px;border-radius:10px;margin-top:10px;border-left:4px solid #16a34a;box-shadow:0 3px 8px rgba(0,0,0,0.05);}
.reply-form{display:flex;gap:6px;margin-top:6px;}
.reply-form input{flex:1;padding:6px;font-size:13px;}
.reply-form button{padding:6px 10px;font-size:13px;background:#16a34a;border:none;border-radius:6px;color:white;}


/* Thread guide lines */
.comment-wrap { display: flex; gap: 0; }
.thread-lines { display: flex; flex-direction: row; flex-shrink: 0; }

.thread-line {
    width: 20px;
    position: relative;
    display: flex;
    justify-content: center;
}
.thread-line::before {
    content: '';
    position: absolute;
    top: 0; bottom: 0; left: 50%;
    width: 2px;
    background: rgba(22,163,74,0.3);
    transform: translateX(-50%);
}

.thread-line-bend {
    width: 20px;
    position: relative;
    flex-shrink: 0;
}
.thread-line-bend::before {
    content: '';
    position: absolute;
    top: 0; left: 50%;
    width: 2px; height: 22px;
    background: rgba(22,163,74,0.3);
    transform: translateX(-50%);
}
.thread-line-bend::after {
    content: '';
    position: absolute;
    top: 22px; left: 50%;
    width: 12px; height: 2px;
    background: rgba(22,163,74,0.3);
}

.thread-line-bend.last::before { height: 22px; }

/* Adjust existing .comment to have left margin when nested */
.comment-wrap .comment {
    flex: 1;
    margin-left: 4px;
    margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="sidebar">
<h2>🌳 TreeKnown</h2>
<a href="?tab=dashboard" class="<?= $tab=='dashboard'?'active':'' ?>">Dashboard</a>
<a href="?tab=submit" class="<?= $tab=='submit'?'active':'' ?>">Submit Tree</a>
<a href="?tab=library" class="<?= $tab=='library'?'active':'' ?>">Tree Library</a>
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
<div class='stat-card'><h3>Total Trees</h3><p>$total</p></div>
<div class='stat-card'><h3>Approved</h3><p>$approved</p></div>
<div class='stat-card'><h3>Pending</h3><p>$pending</p></div>
</div>";
break;

/* SUBMIT TREE */
case 'submit':
echo "<h2>Submit Tree</h2>";
echo '<form method="post" enctype="multipart/form-data">
<input type="text" name="species_guess" placeholder="Tree Guess" required>
<input type="text" name="location_name" placeholder="Location" required>
<p>Click the map to set location</p>
<div id="map"></div>
<input type="hidden" name="lat" id="lat" required>
<input type="hidden" name="lng" id="lng" required>
<input type="file" name="photo" required>
<button name="submit_tree">Submit Tree</button>
</form>';

if($success){
    echo "<p style='color:green;font-weight:bold;margin-top:10px;'>🌳 Tree Submitted Successfully!</p>";
}
?>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const map=L.map('map').setView([8.360288,124.868472],19);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
let marker;
map.on('click',function(e){
    const lat=e.latlng.lat;
    const lng=e.latlng.lng;
    if(marker){ marker.setLatLng(e.latlng); }
    else{ marker=L.marker(e.latlng).addTo(map); }
    document.getElementById('lat').value=lat;
    document.getElementById('lng').value=lng;
});
</script>
<?php
break;

/* TREE LIBRARY */
case 'library':
$trees = $conn->query("
SELECT t.tree_id,s.tree_name,t.species_guess,t.location_name,u.name AS submitted_by,t.photo
FROM TREE_SUBMISSIONS t
JOIN USERS u ON t.submitted_by=u.user_id
LEFT JOIN TREE_LIBRARY s ON t.species_id=s.treelib_id
WHERE t.status='approved'
ORDER BY t.date_submitted DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Tree Library</h2>";
echo "<div class='tree-grid'>";
foreach($trees as $t){
    $photo = $t['photo'] ? "../uploads/".$t['photo'] : "https://via.placeholder.com/150";
    $name = $t['tree_name'] ?? $t['species_guess'];
    echo "<div class='tree-card'>
    <img src='$photo'>
    <h3>$name</h3>
    <p>{$t['location_name']}</p>
    <small>Submitted by {$t['submitted_by']}</small><br><br>
    <a href='?tab=tree&id={$t['tree_id']}' style='background:#16a34a;color:white;padding:8px 12px;border-radius:8px;text-decoration:none;'>💬 View Discussion</a>
    </div>";
}
echo "</div>";
break;

/* TREE DISCUSSION */
case 'tree':
$tree_id = $_GET['id'];
$stmt = $conn->prepare("
SELECT t.*,u.name
FROM TREE_SUBMISSIONS t
JOIN USERS u ON t.submitted_by=u.user_id
WHERE tree_id=?
");
$stmt->execute([$tree_id]);
$tree = $stmt->fetch(PDO::FETCH_ASSOC);
$photo = $tree['photo'] ? "../uploads/".$tree['photo'] : "https://via.placeholder.com/200";

echo "<div class='tree-detail'>
<img src='$photo'>
<div>
<h2>{$tree['species_guess']}</h2>
<p>📍 {$tree['location_name']}</p>
<small>Submitted by {$tree['name']}</small>
</div>
</div>";

echo "<form method='post'>
<input type='hidden' name='tree_id' value='$tree_id'>
<textarea name='comment_text' placeholder='Join the discussion...' required></textarea>
<button name='submit_comment'>Post Comment</button>
</form><hr>";

$comments = $conn->prepare("
SELECT c.*,u.name
FROM COMMENTS c
JOIN USERS u ON c.user_id=u.user_id
WHERE tree_id=?
ORDER BY created_at ASC
");
$comments->execute([$tree_id]);
$comments = $comments->fetchAll(PDO::FETCH_ASSOC);

function displayComments($comments, $parent = null, $level = 0, $parentLines = []) {
    foreach ($comments as $i => $c) {
        if ($c['parent_comment_id'] == $parent) {

            // Check if this is the LAST child at this level
            $siblings = array_filter($comments, fn($x) => $x['parent_comment_id'] == $parent);
            $siblingsArr = array_values($siblings);
            $isLast = ($c['comment_id'] == end($siblingsArr)['comment_id']);

            // Build the vertical guide lines for parent levels
            $linesHTML = '';
            foreach ($parentLines as $hasMore) {
                if ($hasMore) {
                    $linesHTML .= '<div class="thread-line"></div>';
                } else {
                    $linesHTML .= '<div class="thread-line" style="visibility:hidden"></div>';
                }
            }

            // The bend connector for this level
            if ($level > 0) {
                $bendClass = $isLast ? 'thread-line-bend last' : 'thread-line-bend';
                $linesHTML .= '<div class="' . $bendClass . '"></div>';
            }

            echo '<div class="comment-wrap">';
            if ($level > 0) {
                echo '<div class="thread-lines">' . $linesHTML . '</div>';
            }
            echo '<div class="comment">';
            echo '<b>' . htmlspecialchars($c['name']) . '</b> <small>' . $c['created_at'] . '</small><br>';
            echo '<p>' . htmlspecialchars($c['comment_text']) . '</p>';
            echo '<form method="post" class="reply-form">
                <input type="hidden" name="tree_id" value="' . $c['tree_id'] . '">
                <input type="hidden" name="parent_comment_id" value="' . $c['comment_id'] . '">
                <input type="text" name="comment_text" placeholder="Reply..." required>
                <button name="submit_comment">Reply</button>
            </form>';
            echo '</div></div>';

            // Recurse — pass down whether THIS level still has more siblings below
            $newParentLines = array_merge($parentLines, [!$isLast]);
            displayComments($comments, $c['comment_id'], $level + 1, $newParentLines);
        }
    }
}

displayComments($comments);
break;

}
?>

</div>
</body>
</html>