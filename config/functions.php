<?php

function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/index.php');
    }
}

// Log activity function
function logActivity($pdo, $userId, $email, $action, $status) {
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, email, action, status, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $email, $action, $status]);
}

// Render header
function renderHeader($title) {
    $currentEmail = $_SESSION['email'] ?? '';
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <!-- Material Icons -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="http://localhost/php-pdo/assets/css/style.css">
    </head>
    <body>
        <?php if ($isLoggedIn): ?>
            <div class="main-content">
                <div class="container">
        <?php else: ?>
            <div class="container" style="margin: 0 auto; max-width: 500px; padding-top: 100px;">
        <?php endif; ?>
    <?php
}

// Render footer
function renderFooter() {
    $isLoggedIn = isset($_SESSION['user_id']);
    ?>
        <?php if ($isLoggedIn): ?>
                </div>
            </div>
        <?php else: ?>
            </div>
        <?php endif; ?>
    </body>
    </html>
    <?php
}
?>