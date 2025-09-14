<?php
require_once 'database/database_helper.php';

$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$authService = new AuthService($firebaseService);

// Require authentication
$authService->requireAuth();
$currentAdmin = $authService->getCurrentAdmin();

$success = false;
$error = '';

// Ensure current admin role
$isSuperAdmin = ($currentAdmin['role'] ?? '') === 'super_admin';

// Handle admin creation (only super admin)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!$isSuperAdmin) {
        $error = 'Only Super Admins can create administrators.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($name && $email && $password && $role && $status) {
            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } else {
                try {
                    $database = $firebaseService->getDatabase();
                    $admins = $database->getReference('admins')->getValue() ?? [];
                    
                    // Check if email already exists
                    $emailExists = false;
                    foreach ($admins as $admin) {
                        if ($admin['email'] === $email) {
                            $emailExists = true;
                            break;
                        }
                    }
                    
                    if ($emailExists) {
                        $error = 'An administrator with this email already exists';
                    } else {
                        // Create new admin
                        $adminId = $firebaseService->generateAdminId();
                        $hashedPassword = $firebaseService->hashPassword($password);
                        
                        $newAdmin = [
                            'name' => $name,
                            'email' => $email,
                            'password' => $hashedPassword,
                            'role' => $role,
                            'status' => $status,
                            'createdAt' => date('c'),
                            'createdBy' => $currentAdmin['email']
                        ];
                        
                        $database->getReference('admins/' . $adminId)->set($newAdmin);
                        $success = true;
                    }
                } catch (Exception $e) {
                    $error = 'Failed to create administrator. Please try again.';
                }
            }
        } else {
            $error = 'Please fill in all required fields';
        }
    }
}

// Handle status toggle (only super admin)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!$isSuperAdmin) {
        $error = 'Only Super Admins can change admin status.';
    } else {
        $adminId = $_POST['admin_id'] ?? '';
        $newStatus = $_POST['new_status'] ?? '';
        
        if ($adminId && $newStatus && $adminId !== $currentAdmin['id']) {
            try {
                $database = $firebaseService->getDatabase();
                $database->getReference('admins/' . $adminId)->update([
                    'status' => $newStatus,
                    'updatedAt' => date('c'),
                    'updatedBy' => $currentAdmin['email']
                ]);
                $success = true;
            } catch (Exception $e) {
                $error = 'Failed to update administrator status';
            }
        }
    }
}

// Handle admin deletion (only super admin)
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!$isSuperAdmin) {
        $error = 'Only Super Admins can delete administrators.';
    } else {
        $adminId = $_POST['admin_id'] ?? '';
        
        if ($adminId && $adminId !== $currentAdmin['id']) {
            try {
                $database = $firebaseService->getDatabase();
                $database->getReference('admins/' . $adminId)->remove();
                $success = true;
            } catch (Exception $e) {
                $error = 'Failed to delete administrator';
            }
        }
    }
}

// Get admins from Firebase
$database = $firebaseService->getDatabase();
$admins = $database->getReference('admins')->getValue() ?? [];

// Calculate stats
$totalAdmins = count($admins);
$activeAdmins = 0;
$inactiveAdmins = 0;
$superAdmins = 0;

foreach ($admins as $admin) {
    if (($admin['status'] ?? 'inactive') === 'active') {
        $activeAdmins++;
    } else {
        $inactiveAdmins++;
    }
    
    if (($admin['role'] ?? 'admin') === 'super_admin') {
        $superAdmins++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKrow Admin - Admin Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
    <body class="dashboard-page">

    <div class="container-fluid py-4 content-margin">
            <div class="row">
                <div class="col-3">
                    <?php include 'sidebar.php'; ?>
                </div>
                <div class="col-12">
                    <div class="row mb-4 mt-5 pt-4">
                        <div class="col">
                            <h2 class="fw-bold text-dark mb-1">Admin Management</h2>
                            <p class="text-muted">Manage administrator accounts and permissions</p>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                                <i class="fas fa-plus me-2"></i>Create New Admin
                            </button>
                        </div>
                    </div>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            Operation completed successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-user-shield text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h4 class="fw-bold mb-1"><?= $totalAdmins ?></h4>
                                <p class="text-muted mb-0">Total Admins</p>
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
                                <h4 class="fw-bold mb-1"><?= $activeAdmins ?></h4>
                                <p class="text-muted mb-0">Active</p>
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
                                <h4 class="fw-bold mb-1"><?= $inactiveAdmins ?></h4>
                                <p class="text-muted mb-0">Inactive</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-crown text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h4 class="fw-bold mb-1"><?= $superAdmins ?></h4>
                                <p class="text-muted mb-0">Super Admins</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admins Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">All Administrators</h5>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Search admins..." id="searchInput">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (empty($admins)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No administrators found</h5>
                    <p class="text-muted mb-0">No administrators are currently registered</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="adminsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-4 py-3">Administrator</th>
                                <th class="border-0 py-3">Email</th>
                                <th class="border-0 py-3">Role</th>
                                <th class="border-0 py-3">Status</th>
                                <th class="border-0 py-3">Last Login</th>
                                <th class="border-0 py-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $adminId => $admin): ?>
                            <tr class="fade-in admin-row">
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar-sm me-3">
                                            <i class="fas fa-user-shield fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                <?= htmlspecialchars($admin['name'] ?? 'Unknown Admin') ?>
                                                <?php if ($adminId === $currentAdmin['id']): ?>
                                                <small class="text-muted">(You)</small>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">ID: <?= htmlspecialchars($adminId) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium"><?= htmlspecialchars($admin['email'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $role = $admin['role'] ?? 'admin';
                                    $roleBadgeClass = $role === 'super_admin' ? 'bg-warning text-dark' : 'bg-primary';
                                    ?>
                                    <span class="badge <?= $roleBadgeClass ?>"><?= $role === 'super_admin' ? 'Super Admin' : 'Admin' ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status = $admin['status'] ?? 'inactive';
                                    $statusBadgeClass = $status === 'active' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $statusBadgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= isset($admin['lastLogin']) ? date('M j, Y', strtotime($admin['lastLogin'])) : 'Never' ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($adminId !== $currentAdmin['id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="admin_id" value="<?= $adminId ?>">
                                            <input type="hidden" name="new_status" value="<?= $status === 'active' ? 'inactive' : 'active' ?>">
                                            <button type="submit" class="btn btn-outline-primary">
                                                <i class="fas fa-<?= $status === 'active' ? 'pause' : 'play' ?> me-1"></i>
                                                <?= $status === 'active' ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this administrator?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="admin_id" value="<?= $adminId ?>">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                        <?php endif; ?>
                                    </div>
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

    <!-- Create Admin Modal -->
    <div class="modal fade" id="createAdminModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Create New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adminName" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="adminName" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="adminEmail" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="adminEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="adminPassword" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="adminPassword" name="password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleCreatePassword()">
                                    <i class="fas fa-eye" id="toggleCreateIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long</div>
                        </div>

                        <div class="mb-3">
                            <label for="adminRole" class="form-label fw-semibold">Role</label>
                            <select class="form-select" id="adminRole" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="adminStatus" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="adminStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleCreatePassword() {
            const passwordInput = document.getElementById('adminPassword');
            const toggleIcon = document.getElementById('toggleCreateIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-row');
            
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