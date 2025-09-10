<?php
session_start();
require_once '../includes/config.php';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "<h2>Login Attempt</h2>";
    echo "Username: $username<br>";
    echo "Password: $password<br>";
    
    // Check super admin
    $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = ?");
    $stmt->execute([$username]);
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($superAdmin) {
        echo "<p>Super admin found in database</p>";
        echo "Stored hash: " . $superAdmin['password'] . "<br>";
        
        if (password_verify($password, $superAdmin['password'])) {
            echo "<p style='color: green;'>Password verification successful!</p>";
            $_SESSION['super_admin_id'] = $superAdmin['id'];
            $_SESSION['super_admin_username'] = $superAdmin['username'];
            echo "<p>Session set. Redirecting to dashboard...</p>";
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<p style='color: red;'>Password verification failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>Super admin not found!</p>";
    }
    
    // Check race admin
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<p>Race admin found in database</p>";
        echo "Stored hash: " . $admin['password'] . "<br>";
        
        if (password_verify($password, $admin['password'])) {
            echo "<p style='color: green;'>Password verification successful!</p>";
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_race_id'] = $admin['race_id'];
            echo "<p>Session set. Redirecting to dashboard...</p>";
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<p style='color: red;'>Password verification failed!</p>";
        }
    } else {
        echo "<p style='color: red;'>Race admin not found!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Admin Login (Simple Version)</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="superadmin" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" value="superadmin123" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>