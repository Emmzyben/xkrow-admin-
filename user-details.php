<?php
require_once 'database/database_helper.php';

$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$authService = new AuthService($firebaseService);

// Require authentication
$authService->requireAuth();
$currentAdmin = $authService->getCurrentAdmin();

$userId = $_GET['id'] ?? '';
$user = null;
$success = false;
$error = '';

if (!$userId) {
    header('Location: dashboard.php');
    exit;
}

// Get user data
$database = $firebaseService->getDatabase();
$user = $database->getReference('users_table/' . $userId)->getValue();

if (!$user) {
    $error = 'User not found';
} else {
    // Handle status update
    if ($_POST && isset($_POST['status'])) {
        $newStatus = $_POST['status'];
        $notes = trim($_POST['notes'] ?? '');
        
        if (in_array($newStatus, ['approved', 'rejected'])) {
            try {
                $updateData = [
                    'status' => $newStatus,
                    'updatedAt' => date('c'),
                    'updatedBy' => $currentAdmin['email']
                ];
                
                if ($notes) {
                    $updateData['statusNotes'] = $notes;
                }
                
                $database->getReference('users_table/' . $userId)->update($updateData);
                
                // Update local user data
                $user = array_merge($user, $updateData);
                $success = true;
            } catch (Exception $e) {
                $error = 'Failed to update user status. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKrow Admin - User Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
   
</head>
    <body class="dashboard-page">
    

        <div class="container-fluid py-4 content-margin">
            <?php include 'sidebar.php'; ?>
            <div class="row">
                <div class="col-12">
                    <div class="row mb-4">
                        <div class="col">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active">User Details</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

        <?php if ($error && !$user): ?>
        <div class="text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">User Not Found</h5>
            <p class="text-muted mb-3">The requested user could not be found</p>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
        <?php else: ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            User status updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                       <div class=" mx-auto mb-3">
    <?php if (!empty($user['profilePicture'])): ?>
        <img src="<?= htmlspecialchars($user['profilePicture']) ?>" 
             alt="User Avatar" 
             class="rounded-circle" 
             style="width: 200px; height: 200px; object-fit: cover;">
    <?php else: ?>
        <i class="fas fa-user-circle fa-5x text-secondary"></i>
    <?php endif; ?>
</div>

                        <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['firstName'] ) ?> <?= htmlspecialchars($user['lastName'] ) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                        <?php
                        $status = $user['status'] ?? 'pending';
                        $badgeClass = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
                        ?>
                        <span class="badge <?= $badgeClass ?> fs-6 px-3 py-2"><?= ucfirst($status) ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0">User Information</h5>
                    </div>
                
    <div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="card-title mb-4 text-primary fw-bold">
            <i class="fas fa-user me-2"></i>User Information
        </h5>

        <div class="row g-4">
            <!-- Left column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Full Name</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['firstName'] ?? '') ?> <?= htmlspecialchars($user['lastName'] ?? '') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Username</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Email Address</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Email Verified</label>
                    <span class="badge <?= ($user['emailVerified'] ?? 'unverified') === 'verified' ? 'bg-success' : 'bg-danger' ?>">
                        <?= htmlspecialchars($user['emailVerified'] ?? 'unverified') ?>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Phone Number</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['mobileNumber'] ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Phone Verified</label>
                    <span class="badge <?= ($user['phoneVerified'] ?? 'unverified') === 'verified' ? 'bg-success' : 'bg-danger' ?>">
                        <?= htmlspecialchars($user['phoneVerified'] ?? 'unverified') ?>
                    </span>
                </div>
            </div>

            <!-- Right column -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Date of Birth</label>
                    <p class="fw-bold mb-0">
                        <?= !empty($user['dob']) ? date("F j, Y", strtotime($user['dob'])) : 'N/A' ?>
                    </p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Address</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">State</label>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($user['state'] ?? 'N/A') ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Balance</label>
                    <p class="fw-bold mb-0 text-success">₦<?= number_format($user['balance'] ?? 0, 2) ?></p>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Account Status</label>
                    <span class="badge 
                        <?= ($user['status'] ?? 'pending') === 'approved' ? 'bg-success' : 
                           (($user['status'] ?? 'pending') === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                        <?= htmlspecialchars($user['status'] ?? 'pending') ?>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">User ID</label>
                    <p class="fw-bold mb-0 font-monospace"><?= htmlspecialchars($userId ?? 'N/A') ?></p>
                </div>
            </div>

            <!-- Full width ID card preview -->
            <div class="col-12">
                <label class="form-label text-muted fw-semibold">ID Card</label><br>
                <?php if (!empty($user['IDcard'])): ?>
                    <a href="<?= htmlspecialchars($user['IDcard']) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($user['IDcard']) ?>" 
                             alt="ID Card" 
                             class="img-thumbnail shadow-sm rounded" 
                             style="max-width: 180px; height: auto; cursor: pointer;">
                    </a>
                <?php else: ?>
                    <p class="fw-bold mb-0">N/A</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="fw-bold mb-0">Status Management</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row align-items-end">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-semibold">Update Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="approved" <?= ($user['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="rejected" <?= ($user['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </div>
                               
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-1"></i>Update
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-3 text-end">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>