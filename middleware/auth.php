<?php
// middleware/auth.php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../config/config.php';

function generateJWT($userId) {
    $payload = [
        'iss' => 'yourdomain.com',
        'sub' => $userId,
        'iat' => time(),
        'exp' => time() + (60 * 60) // 1 hour
    ];

    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function verifyJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return false;
    }
}



function requireAuth() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }

    if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        $token = $matches[1];
        $decoded = verifyJWT($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        return $decoded; // return full JWT payload (can include user ID, roles, etc.)
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Malformed Authorization header']);
        exit;
    }
}