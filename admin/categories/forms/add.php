<!-- File: admin/forms/add.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Add Form Field';
requireLogin();

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

// If race admin, they can only add form fields to their assigned race
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
    $fieldName = sanitize($_POST['field_name']);
    $fieldType = sanitize($_POST['field_type']);
    $isRequired = isset($_POST['is_required']) ? 1 : 0;
    $fieldOptions = '';
    
    if ($fieldType === 'select') {
        $options = explode("\n", trim($_POST['field_options']));
        $options = array_filter($options, 'trim');
        $fieldOptions = json_encode($options);
    }
    
    if (empty($fieldName) || empty($fieldType)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO forms (race_id, field_name, field_type, is_required, field_options) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$raceId, $fieldName, $fieldType, $isRequired, $fieldOptions]);
            
            $_SESSION['success'] = "Form field added successfully.";
            header("Location: index.php?race_id=" . $raceId);
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error adding form field: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add New Form Field</h1>
        <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Form Fields
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
                        <label for="field_name" class="form-label">Field Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="field_name" name="field_name" required>
                        <div class="invalid-feedback">
                            Please provide a field name.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="field_type" name="field_type" required onchange="toggleFieldOptions()">
                            <option value="">-- Select Field Type --</option>
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="file">File Upload</option>
                            <option value="select">Select/Dropdown</option>
                            <option value="textarea">Textarea</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select a field type.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_required" name="is_required">
                    <label class="form-check-label" for="is_required">Required field</label>
                </div>
                
                <div class="mb-3" id="field_options_container" style="display: none;">
                    <label for="field_options" class="form-label">Options (one per line)</label>
                    <textarea class="form-control" id="field_options" name="field_options" rows="4"></textarea>
                    <div class="form-text">Enter each option on a new line.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $raceId; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Form Field</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFieldOptions() {
    const fieldType = document.getElementById('field_type').value;
    const fieldOptionsContainer = document.getElementById('field_options_container');
    
    if (fieldType === 'select') {
        fieldOptionsContainer.style.display = 'block';
    } else {
        fieldOptionsContainer.style.display = 'none';
    }
}
</script>

<?php include '../../includes/footer.php'; ?>