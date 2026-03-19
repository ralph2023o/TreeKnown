<?php
session_start();
include __DIR__ . '/../config/db.php'; 

// Only teachers can access
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'teacher'){
    die("Access Denied");
}

$tab = $_GET['tab'] ?? 'dashboard';

/* Handle Approve/Reject */
if(isset($_GET['action'], $_GET['id']) && $tab=='verification'){
    $id = intval($_GET['id']);
    if($_GET['action']=='approve'){
        $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='approved' WHERE tree_id=?")->execute([$id]);
    } elseif($_GET['action']=='reject'){
        $conn->prepare("UPDATE TREE_SUBMISSIONS SET status='rejected' WHERE tree_id=?")->execute([$id]);
    }
    header("Location:?tab=verification");
    exit;
}

/* Handle Comment Submission */
if(isset($_POST['submit_comment'])){
    $tree_id = $_POST['tree_id'];
    $comment = $_POST['comment_text'];
    $parent = $_POST['parent_comment_id'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO COMMENTS(tree_id,user_id,parent_comment_id,comment_text)
        VALUES(?,?,?,?)
    ");
    $stmt->execute([$tree_id,$_SESSION['user_id'],$parent,$comment]);
}

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
.sidebar{width:220px;height:100vh;background:#1f4d2b;color:white;position:fixed;}
.sidebar h2{text-align:center;padding:20px;background:#163a20;}
.sidebar a{display:block;padding:15px;color:white;text-decoration:none;border-bottom:1px solid rgba(255,255,255,0.1);}
.sidebar a:hover, .sidebar a.active{background:#3cb371;}

/* MAIN */
.main{margin-left:220px;padding:25px;width:100%;}
header{background:white;padding:15px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);margin-bottom:20px;}

/* CARDS */
.cards{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:25px;}
.card{flex:1;min-width:200px;background:white;padding:20px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);text-align:center;}
.card h3{color:#666;}
.card p{font-size:28px;font-weight:bold;color:#2e8b57;}

/* TABLE */
table{width:100%;border-collapse:collapse;background:white;border-radius:10px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
th{background:#2e8b57;color:white;padding:12px;}
td{padding:10px;border-bottom:1px solid #eee;}
tr:hover{background:#f9f9f9;}

/* BUTTONS */
.btn{padding:6px 12px;border-radius:5px;color:white;text-decoration:none;font-size:13px;}
.approve{background:#28a745;}
.reject{background:#dc3545;}

/* TREE LIBRARY */
.tree-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.tree-card{background:white;border-radius:15px;padding:15px;text-align:center;box-shadow:0 5px 15px rgba(0,0,0,0.1);max-width:250px;margin:auto;}
.tree-card img{width:100%;height:160px;object-fit:cover;border-radius:10px;margin-bottom:10px;}

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

case 'verification':
    $trees = $conn->query("
        SELECT t.tree_id,t.species_guess,s.tree_name,t.location_name,u.name AS submitted_by,t.photo
        FROM TREE_SUBMISSIONS t
        JOIN USERS u ON t.submitted_by=u.user_id
        LEFT JOIN TREE_LIBRARY s ON t.species_id=s.treelib_id
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
    if(count($trees) == 0){
        echo "<p>No approved trees yet.</p>";
    } else {
        echo "<div class='tree-grid'>";
        foreach($trees as $t){
            $photo = $t['photo'] ? "../uploads/{$t['photo']}" : "https://via.placeholder.com/150";
            $tree_name = $t['tree_name'] ?? $t['species_guess'] ?? 'Unknown';
            echo "<div class='tree-card'>
                <img src='$photo' alt='$tree_name'>
                <h3>$tree_name</h3>
                <p>{$t['location_name']}</p>
                <small>Submitted by {$t['submitted_by']}</small><br><br>
                <a href='?tab=tree&id={$t['tree_id']}' style='background:#16a34a;color:white;padding:6px 10px;border-radius:8px;text-decoration:none;'>💬 Discussion</a>
            </div>";
        }
        echo "</div>";
    }
    break;

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
    displayComments($comments->fetchAll(PDO::FETCH_ASSOC));
    break;
}
?>
</div>
</body>
</html>