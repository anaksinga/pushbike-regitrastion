<?php
session_start();

echo "<h1>Session Check</h1>";

// Check if session is working
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>Session is active</p>";
} else {
    echo "<p style='color: red;'>Session is not active</p>";
}

// Set a test session variable
$_SESSION['test'] = 'Session is working';

echo "<p>Test session variable set: " . $_SESSION['test'] . "</p>";

// Check session save path
echo "<p>Session save path: " . session_save_path() . "</p>";

// Check if session directory is writable
if (is_writable(session_save_path())) {
    echo "<p style='color: green;'>Session directory is writable</p>";
} else {
    echo "<p style='color: red;'>Session directory is not writable</p>";
}
?>