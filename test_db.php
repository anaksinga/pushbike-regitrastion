<?php
require_once 'includes/config.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test basic connection
    echo "<p>Database connection: ";
    $pdo->query("SELECT 1");
    echo "SUCCESS</p>";

    // Test getting races
    echo "<p>Testing races query: ";
    $stmt = $pdo->query("SELECT * FROM races");
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($races) . " races</p>";

    // Show race details
    echo "<h2>Race Details:</h2>";
    echo "<ul>";
    foreach ($races as $race) {
        echo "<li>ID: " . $race['id'] . ", Name: " . $race['name'] . "</li>";
    }
    echo "</ul>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}
?>