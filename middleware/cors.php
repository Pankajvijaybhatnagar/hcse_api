<?php
// Allow requests from any origin (for development)
header("Access-Control-Allow-Origin: *");

// If your frontend uses POST, PUT, DELETE etc., also add:
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow custom headers like Content-Type
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Your existing PHP logic below...