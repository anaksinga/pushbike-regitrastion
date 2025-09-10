<!-- File: user/races.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Available Races';
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Get all races
$races = getAllRaces();
// Debug output (remove this in production)
if (empty($races)) {
    echo "<!-- Debug: No races found in database -->";
} else {
    echo "<!-- Debug: Found " . count($races) . " races -->";
}
include '../includes/header.php';
?>
<div class="container my-5">
    <h1 class="text-center mb-5">Available Races</h1>
    
    <?php if (!empty($races)): ?>
    <div class="row">
        <?php foreach ($races as $race): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card race-card h-100">
                <?php if (!empty($race['image'])): ?>
                <img src="/uploads/races/<?php echo $race['image']; ?>" class="card-img-top" alt="<?php echo $race['name']; ?>">
                <?php else: ?>
                <img src="/assets/images/race-placeholder.jpg" class="card-img-top" alt="<?php echo $race['name']; ?>">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo $race['name']; ?></h5>
                    <p class="card-text"><?php echo $race['description'] ?: 'Join this exciting pushbike race!'; ?></p>
                    <div class="mb-3">
                        <p class="mb-1"><i class="bi bi-geo-alt me-2"></i> <?php echo $race['location']; ?></p>
                        <p class="mb-1"><i class="bi bi-calendar-event me-2"></i> <?php echo formatDate($race['event_date']); ?></p>
                    </div>
                    <div class="mt-auto">
                        <a href="categories.php?race_id=<?php echo $race['id']; ?>" class="btn btn-primary w-100">
                            View Categories
                        </a>
                    </div>
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
<?php include '../includes/footer.php'; ?>