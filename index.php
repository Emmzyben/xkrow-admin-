<?php

require_once 'vendor/autoload.php';
require_once 'database/database_helper.php';


$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$authService = new AuthService($firebaseService);

// Redirect if already logged in
if ($authService->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($email && $password) {
        $result = $authService->login($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please enter both email and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKrow Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container-fluid h-100 d-flex align-items-center justify-content-center p-4">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="login-form-container">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5 text-center">
                            <!-- Centered Logo -->
                            <img src="assets/logo.png" alt="logo" 
                                 class="mb-4 mx-auto d-block" style="max-width: 120px;">

                            <h3 class="mb-4 fw-bold">Admin Login</h3>

                            <?php if ($error): ?>
                            <div class="alert alert-danger text-start" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                            <?php endif; ?>

                            <form method="POST" class="text-start">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email" class="form-control border-start-0" 
                                               id="email" name="email" required 
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" 
                                               id="password" name="password" required>
                                        <button class="btn btn-outline-secondary border-start-0" 
                                                type="button" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>