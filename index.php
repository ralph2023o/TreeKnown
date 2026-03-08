<?php
require_once 'config/functions.php';
require_once 'config/db.php';

if (isLoggedIn()) {
    redirect('/app/dashboard.php'); // optional, or handle per table
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = null;
    $dashboard = null;

    // Check admin table
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $dashboard = '/Admin/index.php';
    }

    // Check instructor table if not found in admin
    if (!$dashboard) {
        $stmt = $pdo->prepare("SELECT * FROM instructor WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $dashboard = '/app/instructor/dashboard.php';
        }
    }

    // Check student table if not found yet
    if (!$dashboard) {
        $stmt = $pdo->prepare("SELECT * FROM student WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $dashboard = '/app/student/dashboard.php';
        }
    }

    if ($dashboard) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        // Log successful login
        logActivity($pdo, $user['id'], $user['email'], 'login', 'success');

        // Redirect to correct dashboard
        redirect($dashboard);
    } else {
        // Failed login
        $error = "Invalid credentials";
        logActivity($pdo, null, $email, 'login', 'failed');
    }
}

renderHeader('Login');
?>

<h1>Login</h1>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit">Login</button>
</form>

<?php renderFooter(); ?>