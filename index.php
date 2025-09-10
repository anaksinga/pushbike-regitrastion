<!-- File: index.php -->
<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$pageTitle = 'Home';

// Get all races
$races = getAllRaces();

// Debug output (remove this in production)
if (empty($races)) {
    echo "<!-- Debug: No races found in database -->";
} else {
    echo "<!-- Debug: Found " . count($races) . " races -->";
}

include 'includes/header.php';
?>
 
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h1 class="display-4 fw-bold text-primary mb-4">Welcome to <?php echo APP_NAME; ?></h1>
            <p class="lead mb-5">Register for exciting pushbike races for children of all ages!</p>
            <div class="d-grid gap-4 d-md-flex justify-content-md-center">
                <?php if (!isLoggedIn()): ?>
                
                <?php else: ?>
                <a href="/admin/dashboard.php" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Featured Races Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Available Races</h2>
        </div>
    </div>
    
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
                        <a href="user/categories.php?race_id=<?php echo $race['id']; ?>" class="btn btn-primary w-100">
                            View Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="/user/races.php" class="btn btn-outline-primary">
            View All Races <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-calendar-x fs-1 text-muted mb-3"></i>
        <p class="lead">No races available at the moment.</p>
        <p class="text-muted">Please check back later for upcoming races.</p>
    </div>
    <?php endif; ?>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event fs-1 text-primary mb-3"></i>
                    <h5 class="card-title">Multiple Events</h5>
                    <p class="card-text">Join various pushbike races throughout the year with different categories for all age groups.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 text-primary mb-3"></i>
                    <h5 class="card-title">Community</h5>
                    <p class="card-text">Connect with other parents and children through our exclusive WhatsApp groups after registration.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy fs-1 text-primary mb-3"></i>
                    <h5 class="card-title">Exciting Prizes</h5>
                    <p class="card-text">Win amazing prizes and medals in each category. Every participant gets a certificate!</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bagian testimonial atau informasi tambahan -->
   
    
    
</div>
<?php include 'includes/footer.php'; ?>