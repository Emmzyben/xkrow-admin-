<?php
header("Content-Type: application/json");

// Store Firebase credentials securely
$firebaseConfig = [
    "apiKey" => "AIzaSyDRygHol8peFpvBvpKyyWC70Z9n3FJgZTQ",
    "authDomain" => "xkrow-8372f.firebaseapp.com",
    "databaseURL" => "https://xkrow-8372f-default-rtdb.firebaseio.com",
    "projectId" => "xkrow-8372f",
    "storageBucket" => "xkrow-8372f.firebasestorage.app",
    "messagingSenderId" => "1022772374969",
    "appId" => "1:1022772374969:web:0cfeb7e47fab2fdf451a68",
    "measurementId" => "G-ND63RLHXPS"
];

// Output credentials as JSON
echo json_encode($firebaseConfig);
exit;
?>

