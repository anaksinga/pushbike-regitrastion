<!-- File: user/register.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Registration Form';
// Get category ID from URL
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if ($categoryId <= 0) {
    header("Location: races.php");
    exit;
}
// Get category data
$category = getCategoryById($categoryId);
if (!$category) {
    header("Location: races.php");
    exit;
}
// Check if category is full
if (isCategoryFull($categoryId)) {
    header("Location: categories.php?race_id=" . $category['race_id']);
    exit;
}
// Get race data
$race = getRaceById($category['race_id']);
// Get form fields for this race
$formFields = getFormFieldsByRaceId($category['race_id']);
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participantName = sanitize($_POST['participant_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    
    // Validate required fields
    if (empty($participantName) || empty($email) || empty($phone)) {
        $error = "Please fill in all required fields.";
    } elseif (!isValidEmail($email)) {
        $error = "Please provide a valid email address.";
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Generate unique registration code
            do {
                $registrationCode = generateRegistrationCode();
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM registrations WHERE registration_code = ?");
                $stmt->execute([$registrationCode]);
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } while ($count > 0);
            
            // Insert registration
            $stmt = $pdo->prepare("INSERT INTO registrations (race_id, category_id, registration_code, participant_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category['race_id'], $categoryId, $registrationCode, $participantName, $email, $phone]);
            
            $registrationId = $pdo->lastInsertId();
            
            // Handle proof of transfer upload
            if (isset($_FILES['proof_of_transfer']) && $_FILES['proof_of_transfer']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = pathinfo($_FILES['proof_of_transfer']['name']);
                $fileExtension = strtolower($fileInfo['extension']);
                
                // Validate file extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Invalid file type for proof of transfer. Only JPG, PNG, and PDF files are allowed.");
                }
                
                // Validate file size (max 2MB)
                if ($_FILES['proof_of_transfer']['size'] > 2 * 1024 * 1024) {
                    throw new Exception("File size for proof of transfer must be less than 2MB.");
                }
                
                // Create upload directory if it doesn't exist
                if (!file_exists(UPLOAD_PATH)) {
                    mkdir(UPLOAD_PATH, 0755, true);
                }
                
                // Generate unique filename
                $fileName = $registrationCode . '_proof_' . time() . '.' . $fileExtension;
                $filePath = UPLOAD_PATH . $fileName;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['proof_of_transfer']['tmp_name'], $filePath)) {
                    throw new Exception("Failed to upload proof of transfer.");
                }
                
                // Add to proofs table
                $stmt = $pdo->prepare("INSERT INTO proofs (registration_id, file_path, status) VALUES (?, ?, 'pending')");
                $stmt->execute([$registrationId, $fileName]);
            } else {
                throw new Exception("Please upload proof of transfer.");
            }
            
            // Insert registration details
            foreach ($formFields as $field) {
                $fieldName = 'field_' . $field['id'];
                $fieldValue = isset($_POST[$fieldName]) ? sanitize($_POST[$fieldName]) : '';
                
                // Handle file upload for other fields
                if ($field['field_type'] === 'file' && isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                    $fileInfo = pathinfo($_FILES[$fieldName]['name']);
                    $fileExtension = strtolower($fileInfo['extension']);
                    
                    // Validate file extension
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        throw new Exception("Invalid file type for " . $field['field_name'] . ". Only JPG, PNG, and PDF files are allowed.");
                    }
                    
                    // Validate file size (max 2MB)
                    if ($_FILES[$fieldName]['size'] > 2 * 1024 * 1024) {
                        throw new Exception("File size for " . $field['field_name'] . " must be less than 2MB.");
                    }
                    
                    // Generate unique filename
                    $fileName = $registrationCode . '_' . $field['id'] . '_' . time() . '.' . $fileExtension;
                    $filePath = UPLOAD_PATH . $fileName;
                    
                    // Move uploaded file
                    if (!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $filePath)) {
                        throw new Exception("Failed to upload file for " . $field['field_name']);
                    }
                    
                    $fieldValue = $fileName;
                }
                
                // Insert field value
                $stmt = $pdo->prepare("INSERT INTO registration_details (registration_id, form_field_id, field_value) VALUES (?, ?, ?)");
                $stmt->execute([$registrationId, $field['id'], $fieldValue]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Redirect to success page
            header("Location: success.php?registration_code=" . $registrationCode);
            exit;
        } catch(Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $error = "Error submitting registration: " . $e->getMessage();
        }
    }
}
include '../includes/header.php';
?>
<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Registration Form</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5><?php echo $race['name']; ?></h5>
                        <p class="mb-1"><strong>Category:</strong> <?php echo $category['name']; ?> (Age: <?php echo $category['age_group']; ?>)</p>
                        <p class="mb-1"><strong>Registration Fee:</strong> <?php echo formatCurrency($category['registration_fee']); ?></p>
                        <?php if (!empty($race['nomor_rekening'])): ?>
                        <p class="mb-1"><strong>Nomor Rekening:</strong> <?php echo $race['nomor_rekening']; ?></p>
                        <?php endif; ?>
                        <p class="mb-0"><strong>Location:</strong> <?php echo $race['location']; ?></p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="participant_name" class="form-label">Participant Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="participant_name" name="participant_name" required>
                                <div class="invalid-feedback">
                                    Please provide the participant's name.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">
                                    Please provide a valid email address.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                            <div class="invalid-feedback">
                                Please provide a phone number.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="proof_of_transfer" class="form-label">Proof of Transfer <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="proof_of_transfer" name="proof_of_transfer" required
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">
                                Accepted file types: JPG, PNG, PDF. Max file size: 2MB.
                                <br>Please upload proof of payment for the registration fee.
                                <?php if (!empty($race['nomor_rekening'])): ?>
                                <br><strong>Transfer to:</strong> <?php echo $race['nomor_rekening']; ?>
                                <?php endif; ?>
                            </div>
                            <div class="invalid-feedback">
                                Please upload proof of transfer.
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Additional Information</h5>
                        
                        <?php foreach ($formFields as $field): ?>
                        <div class="mb-3">
                            <label for="field_<?php echo $field['id']; ?>" class="form-label">
                                <?php echo $field['field_name']; ?>
                                <?php if ($field['is_required']): ?>
                                <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if ($field['field_type'] === 'text'): ?>
                            <input type="text" class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
                                   <?php echo $field['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($field['field_type'] === 'email'): ?>
                            <input type="email" class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
                                   <?php echo $field['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($field['field_type'] === 'number'): ?>
                            <input type="number" class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
                                   <?php echo $field['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($field['field_type'] === 'date'): ?>
                            <input type="date" class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
                                   <?php echo $field['is_required'] ? 'required' : ''; ?>>
                            
                            <?php elseif ($field['field_type'] === 'file'): ?>
                            <input type="file" class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
                                   <?php echo $field['is_required'] ? 'required' : ''; ?>
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">
                                Accepted file types: JPG, PNG, PDF. Max file size: 2MB.
                            </div>
                            
                            <?php elseif ($field['field_type'] === 'select'): ?>
                            <select class="form-select" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" 
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
                            <textarea class="form-control" id="field_<?php echo $field['id']; ?>" name="field_<?php echo $field['id']; ?>" rows="3"
                                      <?php echo $field['is_required'] ? 'required' : ''; ?>></textarea>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="terms_check" required>
                                <label class="form-check-label" for="terms_check">
                                    I agree to the terms and conditions and confirm that all information provided is accurate.
                                </label>
                                <div class="invalid-feedback">
                                    You must agree before submitting.
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Registration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>