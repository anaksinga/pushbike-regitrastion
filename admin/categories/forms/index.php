<!-- File: admin/forms/index.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Forms';
requireLogin();

// Get races for filter
if (isSuperAdmin()) {
    $races = getAllRaces();
} else {
    $raceId = $_SESSION['admin_race_id'];
    $races = [$raceId => getRaceById($raceId)];
}

// Get selected race ID from URL
$selectedRaceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

// If no race selected and user is race admin, use their assigned race
if ($selectedRaceId <= 0 && isRaceAdmin()) {
    $selectedRaceId = $_SESSION['admin_race_id'];
}

// Get form fields
if ($selectedRaceId > 0) {
    $formFields = getFormFieldsByRaceId($selectedRaceId);
} else {
    $formFields = [];
}

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Forms</h1>
        <?php if ($selectedRaceId > 0): ?>
        <a href="add.php?race_id=<?php echo $selectedRaceId; ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New Form Field
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="">
                <div class="row">
                    <div class="col-md-6">
                        <label for="race_id" class="form-label">Select Race</label>
                        <select class="form-select" id="race_id" name="race_id" onchange="this.form.submit()">
                            <option value="">-- Select Race --</option>
                            <?php foreach ($races as $race): ?>
                            <option value="<?php echo $race['id']; ?>" <?php echo $selectedRaceId == $race['id'] ? 'selected' : ''; ?>>
                                <?php echo $race['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($selectedRaceId > 0): ?>
    <div class="card">
        <div class="card-body">
            <?php if (count($formFields) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Field Name</th>
                            <th>Field Type</th>
                            <th>Required</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formFields as $field): ?>
                        <tr>
                            <td><?php echo $field['id']; ?></td>
                            <td><?php echo $field['field_name']; ?></td>
                            <td><?php echo ucfirst($field['field_type']); ?></td>
                            <td>
                                <?php if ($field['is_required']): ?>
                                <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit.php?id=<?php echo $field['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $field['id']; ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">No form fields found for this race. <a href="add.php?race_id=<?php echo $selectedRaceId; ?>">Add a new form field</a> to get started.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Preview Form</h5>
        </div>
        <div class="card-body">
            <?php if (count($formFields) > 0): ?>
            <form>
                <?php foreach ($formFields as $field): ?>
                <div class="mb-3">
                    <label for="field_<?php echo $field['id']; ?>" class="form-label">
                        <?php echo $field['field_name']; ?>
                        <?php if ($field['is_required']): ?>
                        <span class="text-danger">*</span>
                        <?php endif; ?>
                    </label>
                    
                    <?php if ($field['field_type'] === 'text'): ?>
                    <input type="text" class="form-control" id="field_<?php echo $field['id']; ?>" 
                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['field_type'] === 'email'): ?>
                    <input type="email" class="form-control" id="field_<?php echo $field['id']; ?>" 
                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['field_type'] === 'number'): ?>
                    <input type="number" class="form-control" id="field_<?php echo $field['id']; ?>" 
                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['field_type'] === 'date'): ?>
                    <input type="date" class="form-control" id="field_<?php echo $field['id']; ?>" 
                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['field_type'] === 'file'): ?>
                    <input type="file" class="form-control" id="field_<?php echo $field['id']; ?>" 
                           <?php echo $field['is_required'] ? 'required' : ''; ?>>
                    
                    <?php elseif ($field['field_type'] === 'select'): ?>
                    <select class="form-select" id="field_<?php echo $field['id']; ?>" 
                            <?php echo $field['is_required'] ? 'required' : ''; ?>>
                        <option value="">-- Select --</option>
                        <?php 
                        $options = json_decode($field['field_options'], true);
                        if (is_array($options)) {
                            foreach ($options as $option) {
                                echo '<option value="' . $option . '">' . $option . '</option>';
                            }
                        }
                        ?>
                    </select>
                    
                    <?php elseif ($field['field_type'] === 'textarea'): ?>
                    <textarea class="form-control" id="field_<?php echo $field['id']; ?>" rows="3"
                              <?php echo $field['is_required'] ? 'required' : ''; ?>></textarea>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <button type="button" class="btn btn-primary" disabled>Submit Registration</button>
            </form>
            <?php else: ?>
            <p class="text-muted">No form fields available. Add form fields to see a preview.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-ui-checks fs-1 text-muted mb-3"></i>
            <p class="text-muted">Please select a race to view its form fields.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>