<?php
require_once 'includes/config.php';

$username = 'superadmin';
$password = 'superadmin123';

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Update existing admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE super_admins SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "<p>Admin account updated successfully!</p>";
    } else {
        // Create new admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO super_admins (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "<p>Admin account created successfully!</p>";
    }
    
    echo "<p>Username: $username</p>";
    echo "<p>Password: $password</p>";
    echo "<p><a href='/admin/login.php'>Go to Login Page</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>