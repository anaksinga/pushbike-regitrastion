<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'Manage Event Photos';
requireLogin();

// Get race ID from URL
$raceId = isset($_GET['race_id']) ? (int)$_GET['race_id'] : 0;

if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get race data
$race = getRaceById($raceId);

if (!$race) {
    $_SESSION['error'] = "Race not found.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Check if the admin has permission to manage this race
if (isRaceAdmin() && $_SESSION['admin_race_id'] != $raceId) {
    $_SESSION['error'] = "You don't have permission to manage photos for this race.";
    header("Location: /admin/dashboard.php");
    exit;
}

// Get all photos for this race
$stmt = $pdo->prepare("SELECT * FROM event_photos WHERE race_id = ? ORDER BY created_at DESC");
$stmt->execute([$raceId]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Event Photos: <?php echo $race['name']; ?></h1>
        <div>
            <a href="/admin/races/index.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to Races
            </a>
            <a href="add.php?race_id=<?php echo $raceId; ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Photo
            </a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>
    
    <?php if (count($photos) > 0): ?>
    <div class="row">
        <?php foreach ($photos as $photo): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card">
                <img src="/uploads/event_photos/<?php echo $photo['photo_path']; ?>" class="card-img-top" alt="<?php echo $photo['caption']; ?>">
                <div class="card-body">
                    <p class="card-text"><?php echo $photo['caption']; ?></p>
                    <div class="d-flex justify-content-between">
                        <a href="edit.php?id=<?php echo $photo['id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="delete.php?id=<?php echo $photo['id']; ?>&race_id=<?php echo $raceId; ?>" class="btn btn-sm btn-outline-danger btn-delete">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-image fs-1 text-muted mb-3"></i>
        <p class="lead">No photos found for this race.</p>
        <p class="text-muted"><a href="add.php?race_id=<?php echo $raceId; ?>">Add your first photo</a> to get started.</p>
    </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>