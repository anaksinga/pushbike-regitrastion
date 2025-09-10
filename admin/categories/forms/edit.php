<!-- File: admin/forms/edit.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Edit Form Field';
requireLogin();

// Get form field ID from URL
$fieldId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($fieldId <= 0) {
    $_SESSION['error'] = "Invalid form field ID.";
    header("Location: index.php");
    exit;
}

// Get form field data
$stmt = $pdo->prepare("SELECT * FROM forms WHERE id = ?");
$stmt->execute([$fieldId]);
$field = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$field) {
    $_SESSION['error'] = "Form field not found.";
    header("Location: index.php");
    exit;
}

// Check if race admin has permission to edit this form field
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $field['race_id']) {
    $_SESSION['error'] = "You don't have permission to edit this form field.";
    header("Location: index.php");
    exit;
}

// Get race data
$race = getRaceById($field['race_id']);

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
            $stmt = $pdo->prepare("UPDATE forms SET field_name = ?, field_type = ?, is_required = ?, field_options = ? WHERE id = ?");
            $stmt->execute([$fieldName, $fieldType, $isRequired, $fieldOptions, $fieldId]);
            
            $_SESSION['success'] = "Form field updated successfully.";
            header("Location: index.php?race_id=" . $field['race_id']);
            exit;
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating form field: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Form Field</h1>
        <a href="index.php?race_id=<?php echo $field['race_id']; ?>" class="btn btn-outline-secondary">
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
                        <input type="text" class="form-control" id="field_name" name="field_name" value="<?php echo $field['field_name']; ?>" required>
                        <div class="invalid-feedback">
                            Please provide a field name.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="field_type" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="field_type" name="field_type" required onchange="toggleFieldOptions()">
                            <option value="">-- Select Field Type --</option>
                            <option value="text" <?php echo $field['field_type'] === 'text' ? 'selected' : ''; ?>>Text</option>
                            <option value="email" <?php echo $field['field_type'] === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="number" <?php echo $field['field_type'] === 'number' ? 'selected' : ''; ?>>Number</option>
                            <option value="date" <?php echo $field['field_type'] === 'date' ? 'selected' : ''; ?>>Date</option>
                            <option value="file" <?php echo $field['field_type'] === 'file' ? 'selected' : ''; ?>>File Upload</option>
                            <option value="select" <?php echo $field['field_type'] === 'select' ? 'selected' : ''; ?>>Select/Dropdown</option>
                            <option value="textarea" <?php echo $field['field_type'] === 'textarea' ? 'selected' : ''; ?>>Textarea</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select a field type.
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_required" name="is_required" <?php echo $field['is_required'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_required">Required field</label>
                </div>
                
                <div class="mb-3" id="field_options_container" style="display: <?php echo $field['field_type'] === 'select' ? 'block' : 'none'; ?>;">
                    <label for="field_options" class="form-label">Options (one per line)</label>
                    <textarea class="form-control" id="field_options" name="field_options" rows="4"><?php 
                    if ($field['field_type'] === 'select' && !empty($field['field_options'])) {
                        $options = json_decode($field['field_options'], true);
                        if (is_array($options)) {
                            echo implode("\n", $options);
                        }
                    }
                    ?></textarea>
                    <div class="form-text">Enter each option on a new line.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?race_id=<?php echo $field['race_id']; ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Form Field</button>
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