<?php
// Sanitize input data
function sanitize($data) {
    global $pdo;
    return htmlspecialchars(trim($data));
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get race by ID
function getRaceById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM races WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getRaceById: " . $e->getMessage());
        return false;
    }
}

// Get category by ID
function getCategoryById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCategoryById: " . $e->getMessage());
        return false;
    }
}

// Get all races
function getAllRaces() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM races ORDER BY event_date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAllRaces: " . $e->getMessage());
        return [];
    }
}

// Get categories by race ID
function getCategoriesByRaceId($raceId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE race_id = ? ORDER BY name");
        $stmt->execute([$raceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getCategoriesByRaceId: " . $e->getMessage());
        return [];
    }
}

// Get form fields by race ID
function getFormFieldsByRaceId($raceId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM forms WHERE race_id = ? ORDER BY id");
        $stmt->execute([$raceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getFormFieldsByRaceId: " . $e->getMessage());
        return [];
    }
}

// Count registrations by category ID
function countRegistrationsByCategoryId($categoryId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM registrations WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch(PDOException $e) {
        error_log("Error in countRegistrationsByCategoryId: " . $e->getMessage());
        return 0;
    }
}

// Check if category is full
function isCategoryFull($categoryId) {
    $category = getCategoryById($categoryId);
    if (!$category) return true;
    
    $count = countRegistrationsByCategoryId($categoryId);
    return $count >= $category['quota'];
}

// Get registration details
function getRegistrationDetails($registrationId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT rd.*, f.field_name, f.field_type 
            FROM registration_details rd
            JOIN forms f ON rd.form_field_id = f.id
            WHERE rd.registration_id = ?
        ");
        $stmt->execute([$registrationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getRegistrationDetails: " . $e->getMessage());
        return [];
    }
}

// Get proof by registration ID
function getProofByRegistrationId($registrationId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM proofs WHERE registration_id = ?");
        $stmt->execute([$registrationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getProofByRegistrationId: " . $e->getMessage());
        return false;
    }
}



// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date) {
    return date('d F Y', strtotime($date));
}
?>