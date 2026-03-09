<?php
if(!isRole('student')){
    die("Access Denied");
}

if(isset($_POST['submit_tree'])){
    $species_id = $_POST['species_id'];
    $location_name = $_POST['location_name'];
    $user_id = $_SESSION['user_id'];

    // Handle photo upload
    $photo = null;
    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = 'tree_'.time().'.'.$ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/'.$photo);
    }

    $stmt = $conn->prepare("INSERT INTO TREE_SUBMISSIONS (species_id, location_name, submitted_by, photo) VALUES (?,?,?,?)");
    $stmt->execute([$species_id, $location_name, $user_id, $photo]);

    echo "<p>Tree submitted successfully!</p>";
}
?>

<h2>Submit New Tree</h2>
<form method="post" enctype="multipart/form-data">
    <label>Species:</label>
    <select name="species_id" required>
        <?php
        $species = $conn->query("SELECT species_id, species_name FROM SPECIES_LIBRARY")->fetchAll(PDO::FETCH_ASSOC);
        foreach($species as $s){
            echo "<option value='{$s['species_id']}'>{$s['species_name']}</option>";
        }
        ?>
    </select><br><br>

    <label>Location:</label>
    <input type="text" name="location_name" required><br><br>

    <label>Photo (optional):</label>
    <input type="file" name="photo"><br><br>

    <button type="submit" name="submit_tree">Submit Tree</button>
</form>