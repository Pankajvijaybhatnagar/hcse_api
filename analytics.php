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

    // Fetch top 3 inquiries by course
    $stmt = $pdo->query("SELECT course, COUNT(*) as count FROM inquiries GROUP BY course ORDER BY count DESC LIMIT 3");
    $topInquiriesByCourse = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total certificates
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates");
    $totalCertificates = (int) $stmt->fetchColumn();

    // Fetch top 3 certificates by course
    $stmt = $pdo->query("SELECT courseName, COUNT(*) as count FROM certificates GROUP BY courseName ORDER BY count DESC LIMIT 3");
    $topCertificatesByCourse = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total certificates issued in the last month
    $stmt = $pdo->query("
        SELECT COUNT(*) as total FROM certificates 
        WHERE certificateIssueDate >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ");
    $totalCertificatesLastMonth = (int) $stmt->fetchColumn();

    // Fetch total distinct courses in last month based on certificates issued
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT courseName) as totalCourses FROM certificates 
        WHERE certificateIssueDate >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    ");
    $totalCoursesLastMonth = (int) $stmt->fetchColumn();

    $response = [
        [
            'name' => 'totalInquiries',
            'value' => $totalInquiries,
        ],
        [
            'name' => 'topInquiriesByCourse',
            'value' => $topInquiriesByCourse,
        ],
        [
            'name' => 'totalCertificates',
            'value' => $totalCertificates,
        ],
        [
            'name' => 'topCertificatesByCourse',
            'value' => $topCertificatesByCourse,
        ],
        [
            'name' => 'totalCertificatesLastMonth',
            'value' => $totalCertificatesLastMonth,
        ],
        [
            'name' => 'totalCoursesLastMonth',
            'value' => $totalCoursesLastMonth,
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
