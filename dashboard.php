<?php
require_once 'database/database_helper.php';

$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$authService = new AuthService($firebaseService);

// Require authentication
$authService->requireAuth();
$currentAdmin = $authService->getCurrentAdmin();

// Get users from Firebase
$database = $firebaseService->getDatabase();
$users = $database->getReference('users_table')->getValue() ?? [];

// Calculate stats
$totalUsers = count($users);
$approvedUsers = 0;
$pendingUsers = 0;
$rejectedUsers = 0;

foreach ($users as $user) {
    $status = $user['status'] ?? 'pending';
    switch ($status) {
        case 'approved':
            $approvedUsers++;
            break;
        case 'rejected':
            $rejectedUsers++;
            break;
        default:
            $pendingUsers++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKrow Admin - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
   
</head>
    <body class="dashboard-page">
       

        <div class="container-fluid py-4 content-margin">
            <?php include 'sidebar.php'; ?>
            <div class="row pt-5">

                <div class="col-12">
                    

                    <!-- Stats Cards -->
                    <div class="row mb-4 mt-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary">
                                            <i class="fas fa-users text-white"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="fw-bold mb-1"><?= $totalUsers ?></h4>
                                            <p class="text-muted mb-0">Total Users</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success">
                                            <i class="fas fa-user-check text-white"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="fw-bold mb-1"><?= $approvedUsers ?></h4>
                                            <p class="text-muted mb-0">Approved</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning">
                                            <i class="fas fa-user-clock text-white"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="fw-bold mb-1"><?= $pendingUsers ?></h4>
                                            <p class="text-muted mb-0">Pending</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-danger">
                                            <i class="fas fa-user-times text-white"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="fw-bold mb-1"><?= $rejectedUsers ?></h4>
                                            <p class="text-muted mb-0">Rejected</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="fw-bold mb-0">All Users</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" placeholder="Search users..." id="searchInput">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No users found</h5>
                                <p class="text-muted mb-0">No users are currently registered in the system</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="usersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-4 py-3">User</th>
                                            <th class="border-0 py-3">Email</th>
                                            <th class="border-0 py-3">Status</th>
                                            <th class="border-0 py-3 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $userId => $user): ?>
                                        <tr class="fade-in user-row" style="cursor: pointer;" onclick="viewUser('<?= $userId ?>')">
                                            <td class="px-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar-sm me-3">
    <?php if (!empty($user['profilePicture'])): ?>
        <img src="<?= htmlspecialchars($user['profilePicture']) ?>" 
             alt="Profile Picture" 
             class="rounded-circle" 
             style="width: 40px; height: 40px; object-fit: cover;">
    <?php else: ?>
        <i class="fas fa-user-circle fa-2x text-secondary"></i>
    <?php endif; ?>
</div>

                                                    <div>
                                                        <div class="fw-bold"><?= htmlspecialchars($user['firstName'] ) ?> <?= htmlspecialchars($user['lastName'] ) ?></div>
                                                        <small class="text-muted">ID: <?= htmlspecialchars($userId) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = $user['status'] ?? 'pending';
                                                $badgeClass = $status === 'approved' ? 'bg-success' : ($status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                          
                                            <td class="text-end">
                                                <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); viewUser('<?= $userId ?>')">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewUser(userId) {
            window.location.href = 'user-details.php?id=' + userId;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>