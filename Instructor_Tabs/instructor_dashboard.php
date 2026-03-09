<?php
// instructor_dashboard.php

// Placeholder data for students/uploads
$submissions = [
    [
        "id" => 1,
        "name" => "John Doe",
        "email" => "john@example.com",
        "info_uploaded" => "Transcript.pdf",
        "profile_pic" => "https://via.placeholder.com/80",
        "status" => "Pending"
    ],
    [
        "id" => 2,
        "name" => "Jane Smith",
        "email" => "jane@example.com",
        "info_uploaded" => "Resume.docx",
        "profile_pic" => "https://via.placeholder.com/80",
        "status" => "Pending"
    ],
    [
        "id" => 3,
        "name" => "Bob Johnson",
        "email" => "bob@example.com",
        "info_uploaded" => "Portfolio.pdf",
        "profile_pic" => "https://via.placeholder.com/80",
        "status" => "Pending"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 10px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .profile-pic {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <h4>Instructor Panel</h4>
            <a href="#">Dashboard</a>
            <a href="#">Submissions</a>
            <a href="#">Students</a>
            <a href="#">Settings</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h2>Submissions Review</h2>

            <div class="card mt-4 p-3">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Profile Pic</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Uploaded Info</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo $submission['id']; ?></td>
                            <td><img src="<?php echo $submission['profile_pic']; ?>" class="profile-pic" alt="Profile"></td>
                            <td><?php echo $submission['name']; ?></td>
                            <td><?php echo $submission['email']; ?></td>
                            <td><?php echo $submission['info_uploaded']; ?></td>
                            <td><?php echo $submission['status']; ?></td>
                            <td>
                                <form method="post" action="process_submission.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="post" action="process_submission.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>