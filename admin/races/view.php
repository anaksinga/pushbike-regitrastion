<!-- File: admin/races/view.php -->
<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
$pageTitle = 'View Race';
requireLogin();
requireSuperAdmin();
// Get race ID from URL
$raceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($raceId <= 0) {
    $_SESSION['error'] = "Invalid race ID.";
    header("Location: index.php");
    exit;
}
// Get race data
$race = getRaceById($raceId);
if (!$race) {
    $_SESSION['error'] = "Race not found.";
    header("Location: index.php");
    exit;
}
// Get photos for this race
$stmt = $pdo->prepare("SELECT * FROM event_photos WHERE race_id = ? ORDER BY created_at DESC");
$stmt->execute([$raceId]);
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
include '../../includes/header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>View Race: <?php echo $race['name']; ?></h1>
        <div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Races
            </a>
            <a href="edit.php?id=<?php echo $race['id']; ?>" class="btn btn-outline-primary ms-2">
                <i class="bi bi-pencil me-2"></i>Edit Race
            </a>
            <a href="/admin/event_photos/index.php?race_id=<?php echo $race['id']; ?>" class="btn btn-outline-info ms-2">
                <i class="bi bi-images me-2"></i>Manage Photos
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
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Race Details</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Name:</th>
                            <td><?php echo $race['name']; ?></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td><?php echo $race['description'] ?: 'No description'; ?></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td><?php echo $race['location']; ?></td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo formatDate($race['event_date']); ?></td>
                        </tr>
                        <tr>
                            <th>Nomor Rekening:</th>
                            <td><?php echo $race['nomor_rekening'] ?: 'Not set'; ?></td>
                        </tr>
                        <tr>
                            <th>WhatsApp Group:</th>
                            <td>
                                <?php if ($race['whatsapp_group_link']): ?>
                                <a href="<?php echo $race['whatsapp_group_link']; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-whatsapp me-1"></i> Join Group
                                </a>
                                <?php else: ?>
                                <span class="text-muted">Not set</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h5>Quick Stats</h5>
                    <div class="row">
                        <div class="col-6">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <?php
                                    // Get categories count
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE race_id = ?");
                                    $stmt->execute([$raceId]);
                                    $categoriesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                    ?>
                                    <h3><?php echo $categoriesCount; ?></h3>
                                    <p class="text-muted mb-0">Categories</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card text-center mb-3">
                                <div class="card-body">
                                    <?php
                                    // Get registrations count
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM registrations WHERE race_id = ?");
                                    $stmt->execute([$raceId]);
                                    $registrationsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                                    ?>
                                    <h3><?php echo $registrationsCount; ?></h3>
                                    <p class="text-muted mb-0">Registrations</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Race Photos</h5>
            <a href="/admin/event_photos/add.php?race_id=<?php echo $raceId; ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Add Photo
            </a>
        </div>
        <div class="card-body">
            <?php if (count($photos) > 0): ?>
            <div class="row">
                <?php foreach ($photos as $photo): ?>
                <div class="col-md-3 col-lg-2 mb-3">
                    <div class="card h-100">
                        <img src="/uploads/event_photos/<?php echo $photo['photo_path']; ?>" class="card-img-top" alt="<?php echo $photo['caption']; ?>" style="height: 150px; object-fit: cover;">
                        <div class="card-body p-2">
                            <p class="card-text small text-truncate"><?php echo $photo['caption']; ?></p>
                            <div class="d-flex justify-content-between">
                                <a href="/admin/event_photos/edit.php?id=<?php echo $photo['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/event_photos/delete.php?id=<?php echo $photo['id']; ?>&race_id=<?php echo $raceId; ?>" class="btn btn-sm btn-outline-danger btn-delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-image fs-1 text-muted mb-3"></i>
                <p class="text-muted">No photos uploaded for this race yet.</p>
                <a href="/admin/event_photos/add.php?race_id=<?php echo $raceId; ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add First Photo
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>