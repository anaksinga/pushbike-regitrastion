<!-- File: includes/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <header class="py-3 mb-4 border-bottom">
            <div class="container d-flex flex-wrap justify-content-between">
                <a href="/" class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-dark text-decoration-none">
                    <i class="bi bi-bicycle fs-2 me-2 text-primary"></i>
                    <span class="fs-4"><?php echo APP_NAME; ?></span>
                </a>
                <?php if (isLoggedIn()): ?>
<div class="dropdown">
    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['admin_username'] ?? $_SESSION['super_admin_username']; ?>
    </button>
    <ul class="dropdown-menu">
        <?php if (isSuperAdmin()): ?>
        <li><a class="dropdown-item" href="/admin/admins/index.php"><i class="bi bi-people me-1"></i> Manage Admins</a></li>
        <li><hr class="dropdown-divider"></li>
        <?php endif; ?>
        <li><a class="dropdown-item" href="/admin/logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a></li>
    </ul>
</div>
<?php endif; ?>
            
           
            
        </header>