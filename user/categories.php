<!-- File: user/categories.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
$pageTitle = 'Race Categories';

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

if ($raceId <= 0) {
    header("Location: races.php");
    exit;
}

// Get race data
$race = getRaceById($raceId);

if (!$race) {
    header("Location: races.php");
    exit;
}

// Get categories for this race
$categories = getCategoriesByRaceId($raceId);

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $race['name']; ?> - Categories</h1>
        <a href="races.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Races
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><i class="bi bi-geo-alt me-2"></i> <strong>Location:</strong> <?php echo $race['location']; ?></p>
                    <p class="mb-1"><i class="bi bi-calendar-event me-2"></i> <strong>Date:</strong> <?php echo formatDate($race['event_date']); ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><?php echo $race['description'] ?: 'Join this exciting pushbike race!'; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (count($categories) > 0): ?>
    <div class="row">
        <?php foreach ($categories as $category): ?>
        <?php
        $registrationCount = countRegistrationsByCategoryId($category['id']);
        $isFull = isCategoryFull($category['id']);
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card category-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $category['name']; ?></h5>
                    <p class="quota-info mb-2">
                        <i class="bi bi-people me-1"></i> 
                        Quota: <?php echo $registrationCount; ?> / <?php echo $category['quota']; ?>
                    </p>
                    <p class="age-group mb-2">
                        <i class="bi bi-person-badge me-1"></i> 
                        Age Group: <?php echo $category['age_group']; ?>
                    </p>
                    <p class="fee-info mb-3">
                        <i class="bi bi-currency-exchange me-1"></i> 
                        <?php echo formatCurrency($category['registration_fee']); ?>
                    </p>
                    
                    <div class="progress mb-3" style="height: 10px;">
                        <?php
                        $percentage = ($category['quota'] > 0) ? ($registrationCount / $category['quota']) * 100 : 0;
                        ?>
                        <div class="progress-bar <?php echo $isFull ? 'bg-danger' : ($percentage >= 75 ? 'bg-warning' : 'bg-success'); ?>" 
                             role="progressbar" style="width: <?php echo min($percentage, 100); ?>%;" 
                             aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <?php if ($isFull): ?>
                    <button class="btn btn-danger w-100" disabled>
                        <i class="bi bi-x-circle me-1"></i> Sold Out
                    </button>
                    <?php else: ?>
                    <a href="register.php?category_id=<?php echo $category['id']; ?>" class="btn btn-primary w-100">
                        <i class="bi bi-pencil-square me-1"></i> Register Now
                    </a>
                    <?php endif; ?>
                    
                    
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Event Photos</h5>
    </div>
    <div class="card-body">
        <?php
        // Get photos for this race
        $stmt = $pdo->prepare("SELECT * FROM event_photos WHERE race_id = ? ORDER BY created_at DESC LIMIT 6");
        $stmt->execute([$raceId]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($photos) > 0):
        ?>
        <div class="row">
            <?php foreach ($photos as $photo): ?>
            <div class="col-md-4 col-lg-2 mb-3">
                <img src="/uploads/event_photos/<?php echo $photo['photo_path']; ?>" class="img-fluid rounded" alt="<?php echo $photo['caption']; ?>">
                <p class="small text-center mt-1"><?php echo $photo['caption']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted">No photos available for this race.</p>
        <?php endif; ?>
    </div>
</div>
        
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-tags fs-1 text-muted mb-3"></i>
        <p class="lead">No categories available for this race.</p>
        <p class="text-muted">Please check back later or contact the race organizer.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>