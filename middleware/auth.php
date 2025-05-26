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
