<?php
include_once '../class/token.php'; // Include the Token class

// Initialize the Token class
$tokenClass = new Token();

// Get the token from the Authorization header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

// Validate the token
if (!$tokenClass->validateToken($token)) {
    http_response_code(401); // Unauthorized
    echo json_encode(array("status" => "error", "message" => "Invalid or expired token"));
    exit();
}

// Decode the token to retrieve user data if needed
// $decodedToken = $tokenClass->decodeToken($token);