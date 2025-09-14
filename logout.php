<?php
require_once 'database/database_helper.php';

$database = getFirebaseDatabase();
$firebaseService = new FirebaseService($database);
$authService = new AuthService($firebaseService);

$authService->logout();
header('Location: index.php');
exit;
?>