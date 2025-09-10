<?php
require_once 'includes/config.php';

try {
    $stmt = $pdo->prepare("INSERT INTO races (name, description, location, event_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        "Test Race",
        "This is a test race for the pushbike registration system",
        "Test Location",
        "2023-12-31"
    ]);
    
    echo "Race added successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>