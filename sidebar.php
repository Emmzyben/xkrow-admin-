<?php
$currentPage = $currentPage ?? '';

$nav = '
<ul class="nav flex-column">
    <li class="nav-item mb-2">
        <a class="nav-link px-3 py-2 rounded ' . ($currentPage === 'dashboard' ? 'active' : '') . '" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link px-3 py-2 rounded ' . ($currentPage === 'admin-management' ? 'active' : '') . '" href="admin-management.php">
            <i class="fas fa-user-shield me-2"></i>Admin Management
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link px-3 py-2 rounded" href="#">
            <i class="fas fa-cog me-2"></i>Settings
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link px-3 py-2 rounded" href="#">
            <i class="fas fa-chart-bar me-2"></i>Reports
        </a>
    </li>
    <li class="nav-item mb-2">
        <a class="nav-link px-3 py-2 rounded" href="#">
            <i class="fas fa-bell me-2"></i>Notifications
        </a>
    </li>
</ul>
';
?>

<!-- Single Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start sidebar-custom" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header d-flex justify-content-between align-items-center border-bottom">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-circle me-2 fs-3 text-secondary"></i>
            <div>
                <div class="fw-bold">
                    <?= htmlspecialchars($currentAdmin['name'] ?? $currentAdmin['email']) ?>
                </div>
                <small class="text-muted">Admin</small>
            </div>
        </div>
        <div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body">
        <h6 class="text-uppercase text-muted mb-3">Navigation</h6>
        <?php echo $nav; ?>
    </div>
</div>

<!-- Sidebar Toggle Button -->
<nav class="navbar navbar-light bg-dark fixed-top border-bottom shadow-sm">
  <div class="container-fluid">
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
        <i class="fas fa-bars"></i>
    </button>
    <span class="navbar-brand fw-bold mb-0 h1 text-primary">XKrow Admin</span>
  </div>
</nav>

<!-- Custom Sidebar Styles -->
<style>
.sidebar-custom {
    background-color: #fff;
    border-right: 1px solid #e5e5e5;
}
.sidebar-custom .nav-link {
    color: #900bd7;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}
.sidebar-custom .nav-link:hover {
    background-color: #900bd7;
    color: #fff;
}
.sidebar-custom .nav-link.active {
    background-color: #900bd7;
    color: #fff !important;
}
</style>
