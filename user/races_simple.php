<?php
require_once '../includes/config.php';

// Direct database query instead of using the function
try {
    $stmt = $pdo->query("SELECT * FROM races ORDER BY event_date DESC");
    $races = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Races</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-5">Available Races</h1>
        
        <?php if (count($races) > 0): ?>
        <div class="row">
            <?php foreach ($races as $race): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <img src="/assets/images/race-placeholder.jpg" class="card-img-top" alt="<?php echo $race['name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $race['name']; ?></h5>
                        <p class="card-text"><?php echo $race['description'] ?: 'Join this exciting pushbike race!'; ?></p>
                        <p><i class="bi bi-geo-alt me-2"></i> <?php echo $race['location']; ?></p>
                        <p><i class="bi bi-calendar-event me-2"></i> <?php echo date('d F Y', strtotime($race['event_date'])); ?></p>
                        <a href="categories.php?race_id=<?php echo $race['id']; ?>" class="btn btn-primary w-100">
                            View Categories
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
            <p class="lead">No races available at the moment.</p>
            <p class="text-muted">Please check back later for upcoming races.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>