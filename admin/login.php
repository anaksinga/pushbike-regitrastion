<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Admin Login';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Check super admin
        $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = ?");
        $stmt->execute([$username]);
        $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($superAdmin && password_verify($password, $superAdmin['password'])) {
            $_SESSION['super_admin_id'] = $superAdmin['id'];
            $_SESSION['super_admin_username'] = $superAdmin['username'];
            header("Location: dashboard.php");
            exit;
        }
        
        // Check race admin
        $stmt = $pdo->prepare("SELECT a.*, r.name as race_name FROM admins a LEFT JOIN races r ON a.race_id = r.id WHERE a.username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_race_id'] = $admin['race_id'];
            $_SESSION['admin_race_name'] = $admin['race_name'];
            header("Location: dashboard.php");
            exit;
        }
        
        $error = "Invalid username or password.";
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-4 text-primary">Admin Login</h1>
                    
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="/" class="text-decoration-none">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>