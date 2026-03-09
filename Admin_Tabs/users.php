<?php
include __DIR__ . '/../config/db.php';
$stmt = $conn->query("SELECT user_id, name, email, role, student_id FROM USERS ORDER BY role, name");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>All Users</h2>";

if(count($users) == 0){
    echo "<p>No users found.</p>";
} else {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Student ID</th></tr>";
    foreach($users as $u){
        echo "<tr>
            <td>{$u['user_id']}</td>
            <td>{$u['name']}</td>
            <td>{$u['email']}</td>
            <td>{$u['role']}</td>
            <td>{$u['student_id']}</td>
        </tr>";
    }
    echo "</table>";
}
?>