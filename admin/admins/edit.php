<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Edit Admin';
requireLogin();
requireSuperAdmin();

// Get admin ID from URL
$adminId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($adminId <= 0) {
    $_SESSION['error'] = "Invalid admin ID.";
    header("Location: index.php");
    exit;
}

// Get admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: index.php");
    exit;
}

// Get all races for the dropdown
$races = getAllRaces();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $raceId = (int)$_POST['race_id'];
    $password = $_POST['password'];
    
    if (empty($username)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            // Check if username already exists (excluding current admin)
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND id != ?");
            $stmt->execute([$username, $adminId]);
            $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingAdmin) {
                $_SESSION['error'] = "Username already exists. Please choose a different username.";
            } else {
                // Update admin
                if (!empty($password)) {
                    // Update with new password
                    if (strlen($password) < 6) {
                        $_SESSION['error'] = "Password must be at least 6 characters long.";
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ?, race_id = ? WHERE id = ?");
                        $stmt->execute([$username, $hashedPassword, $raceId, $adminId]);
                        
                        $_SESSION['success'] = "Admin updated successfully.";
                        header("Location: index.php");
                        exit;
                    }
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, race_id = ? WHERE id = ?");
                    $stmt->execute([$username, $raceId, $adminId]);
                    
                    $_SESSION['success'] = "Admin updated successfully.";
                    header("Location: index.php");
                    exit;
                }
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating admin: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Admin</h1>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Admins
        </a>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post" action="" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $admin['username']; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a username.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6">
                        <div class="form-text">Leave blank to keep current password. If you want to change it, password must be at least 6 characters long.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="race_id" class="form-label">Assign to Race <span class="text-danger">*</span></label>
                    <select class="form-select" id="race_id" name="race_id" required>
                        <option value="">-- Select Race --</option>
                        <?php foreach ($races as $race): ?>
                        <option value="<?php echo $race['id']; ?>" <?php echo $admin['race_id'] == $race['id'] ? 'selected' : ''; ?>>
                            <?php echo $race['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Please select a race.
                    </div>
                    <div class="form-text">This admin will only be able to manage the selected race.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>