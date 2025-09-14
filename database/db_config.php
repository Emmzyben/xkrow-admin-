<?php
require __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;

function getFirebaseDatabase() {
    // Path to your credentials file
    $serviceAccountPath = __DIR__ . '/xkrow-8372f-firebase-adminsdk-fbsvc-fc688dd8f1.json';

    // Safety check: file exists & readable
    if (!file_exists($serviceAccountPath)) {
        die("Firebase service account file not found at: $serviceAccountPath");
    }

    try {
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://xkrow-8372f-default-rtdb.firebaseio.com/');

        return $factory->createDatabase();
    } catch (\Throwable $e) {
        die('Firebase connection failed: ' . $e->getMessage());
    }
}
