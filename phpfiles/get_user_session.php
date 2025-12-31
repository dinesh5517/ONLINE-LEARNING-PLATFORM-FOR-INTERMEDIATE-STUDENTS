<?php
// Start session
session_start();

// Check if user is logged in
$response = [
    'logged_in' => false
];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response = [
        'logged_in' => true,
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email']
    ];
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>