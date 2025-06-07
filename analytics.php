<?php

include __DIR__ . '/middleware/cors.php';
include __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/config/db.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

try {
    $pdo = getDbConnection();

    // Fetch total inquiries
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inquiries");
    $totalInquiries = (int) $stmt->fetchColumn();

    // Fetch total certificates
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates");
    $totalCertificates = (int) $stmt->fetchColumn();

    // Prepare response with only two fields
    $response = [
        [
            'name' => 'totalInquiries',
            'value' => $totalInquiries,
        ],
        [
            'name' => 'totalCertificates',
            'value' => $totalCertificates,
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
