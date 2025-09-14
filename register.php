<?php
require_once 'database/database_helper.php';

$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$success = false;
$error = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    if ($name && $email && $password && $confirmPassword) {
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
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
                    $error = 'An admin with this email already exists';
                } else {
                    // Create new admin
                    $adminId = $firebaseService->generateAdminId();
                    $hashedPassword = $firebaseService->hashPassword($password);
                    
                    $newAdmin = [
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => 'admin',
                        'status' => 'active',
                        'createdAt' => date('c'),
                        'createdBy' => 'self-registration'
                    ];
                    
                    $database->getReference('admins/' . $adminId)->set($newAdmin);
                    $success = true;
                }
            } catch (Exception $e) {
                $error = 'Failed to create admin account. Please try again.';
            }
        }
    } else {
        $error = 'Please fill in all required fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKrow Admin - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container-fluid h-100 p-4">
        <div class="row h-100">
            <div class="col-lg-6 d-none d-lg-flex bg-primary-gradient align-items-center justify-content-center">
                <div class="text-center text-white">
                    <i class="fas fa-crown fa-5x mb-4 text-warning"></i>
                    <h2 class="fw-bold mb-3">XKrow Admin</h2>
                    <p class="lead">Create your admin account to get started</p>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="login-form-container">
                    <div class="text-center mb-4 d-lg-none">
                        <i class="fas fa-crown fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold text-primary">XKrow Admin</h2>
                    </div>
                    
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h3 class="text-center mb-4 fw-bold">Register Admin</h3>
                            
                            <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                Admin account created successfully! <a href="index.php" class="alert-link">Login now</a>
                            </div>
                            <?php elseif ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!$success): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email" class="form-control border-start-0" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="password" name="password" required minlength="6">
                                        <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password', 'toggleIcon1')">
                                            <i class="fas fa-eye" id="toggleIcon1"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Password must be at least 6 characters long</div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" required minlength="6">
                                        <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                            <i class="fas fa-eye" id="toggleIcon2"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Create Admin Account
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <div class="text-center">
                                <p class="text-muted mb-0">Already have an account?</p>
                                <a href="index.php" class="text-decoration-none">Login here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
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
    </script>
</body>
</html>