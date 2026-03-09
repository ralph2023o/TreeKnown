<?php
include __DIR__ . '/../config/db.php';
$stmt = $conn->query("
    SELECT t.tree_id, s.species_name, t.location_name, u.name AS submitted_by, t.photo
    FROM TREE_SUBMISSIONS t
    JOIN USERS u ON t.submitted_by = u.user_id
    JOIN SPECIES_LIBRARY s ON t.species_id = s.species_id
    WHERE t.status='pending'
    ORDER BY t.date_submitted DESC
");
$trees = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Pending Trees (Teacher)</h2>";
if(count($trees)==0){
    echo "<p>No pending trees.</p>";
} else {
    echo "<ul>";
    foreach($trees as $t){
        echo "<li>
            <strong>Tree ID:</strong> {$t['tree_id']} |
            <strong>Species:</strong> {$t['species_name']} |
            <strong>Location:</strong> {$t['location_name']} |
            <strong>Submitted by:</strong> {$t['submitted_by']}<br>";
        if(!empty($t['photo'])){
            echo "<img src='../uploads/{$t['photo']}' width='150'><br>";
        }
        echo "<a href='../verify_tree.php?id={$t['tree_id']}&action=approve'>Approve</a> | 
              <a href='../verify_tree.php?id={$t['tree_id']}&action=reject'>Reject</a>
        </li>";
    }
    echo "</ul>";
}
?>