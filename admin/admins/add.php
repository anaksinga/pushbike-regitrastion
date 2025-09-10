<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Add Admin';
requireLogin();
requireSuperAdmin();

// Get all races for the dropdown
$races = getAllRaces();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $raceId = (int)$_POST['race_id'];
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingAdmin) {
                $_SESSION['error'] = "Username already exists. Please choose a different username.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new admin
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, race_id) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $raceId]);
                
                $_SESSION['success'] = "Admin added successfully.";
                header("Location: index.php");
                exit;
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error adding admin: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add New Admin</h1>
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
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="invalid-feedback">
                            Please provide a username.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="invalid-feedback">
                            Password must be at least 6 characters long.
                        </div>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="race_id" class="form-label">Assign to Race <span class="text-danger">*</span></label>
                    <select class="form-select" id="race_id" name="race_id" required>
                        <option value="">-- Select Race --</option>
                        <?php foreach ($races as $race): ?>
                        <option value="<?php echo $race['id']; ?>"><?php echo $race['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        Please select a race.
                    </div>
                    <div class="form-text">This admin will only be able to manage the selected race.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>