<?php
require_once '../includes/config.php';

// Redirect to the last visited page
if (isset($_SESSION['current_page'])) {
    header("Location: " . $_SESSION['current_page']);
} else {
    header("Location: /admin/dashboard.php");
}
exit;