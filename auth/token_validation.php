<?php
include_once '../include/header.php';
include_once '../vendor/firebase/php-jwt/src/JWT.php';
include_once '../vendor/firebase/php-jwt/src/Key.php';
include_once '../class/token.php'; // Include the Token class

// Initialize the Token class
$tokenClass = new Token();

// Get the token from the Authorization header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

// Validate the token
if (!$tokenClass->validateToken($token)) {
    // If the token is invalid, respond with a 401 Unauthorized status
    http_response_code(401); // Unauthorized
    echo json_encode(array("status" => "error", "message" => "Invalid or expired token"));
    exit();
} else {
    // If the token is valid, you can decode the token to get user data
    $decodedToken = $tokenClass->decodeToken($token);

    // Respond with a success message and decoded token data
    http_response_code(200); // OK
    echo json_encode(array("status" => "success", "message" => "Token is valid"));
}
?>
