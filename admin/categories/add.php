<!-- File: admin/categories/add.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Add Category';
requireLogin();

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

// If race admin, they can only add categories to their assigned race
if (isRaceAdmin()) {
    $raceId = $_SESSION['admin_race_id'];
}

if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: index.php");
    exit;
}

// Get race data
$race = getRaceById($raceId);

if (!$race) {
    $_SESSION['error'] = "Race not found.";
    header("Location: index.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $quota = (int)$_POST['quota'];
    $ageGroup = sanitize($_POST['age_group']);
    $registrationFee = (float)$_POST['registration_fee'];
    
    if (empty($name) || empty($ageGroup) || $quota <= 0 || $registrationFee < 0) {
        $_SESSION['error'] = "Please fill in all fields with valid values.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (race_id, name, quota, age_group, registration_fee) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$raceId, $name, $quota, $ageGroup, $registrationFee]);
            
            $_SESSION['success'] = "Category added successfully.";
            header("Location: index.php?race_id=" . $raceId);
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error adding category: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add New Category</h1>
        <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Categories
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
                <div class="mb-3">
                    <label class="form-label">Race</label>
                    <input type="text" class="form-control" value="<?php echo $race['name']; ?>" readonly>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">
                            Please provide a category name.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="age_group" name="age_group" placeholder="e.g., 5-7 years" required>
                        <div class="invalid-feedback">
                            Please provide an age group.
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quota" class="form-label">Quota <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quota" name="quota" min="1" required>
                        <div class="invalid-feedback">
                            Please provide a valid quota (minimum 1).
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="registration_fee" class="form-label">Registration Fee (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="registration_fee" name="registration_fee" min="0" step="0.01" required>
                        <div class="invalid-feedback">
                            Please provide a valid registration fee.
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>