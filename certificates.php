<?php
include __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/functions/certificateFunctions.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$enrollmentIid = null;

if (preg_match('/\/certificates\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
}

switch ($method) {
    case 'GET':
        if ($enrollmentIid) {
            $cert = getCertificateById($enrollmentIid);
            echo json_encode($cert ?: ['error' => 'Certificate not found']);
        } else {
            echo json_encode(getAllCertificates());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $insertedId = createCertificate($data);
        echo json_encode(['message' => 'Certificate created', 'id' => $insertedId]);
        break;

    case 'PUT':
    case 'PATCH':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Certificate ID required']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (updateCertificate($id, $data)) {
            echo json_encode(['message' => 'Certificate updated']);
        } else {
            echo json_encode(['error' => 'Update failed']);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Certificate ID required']);
            exit;
        }
        if (deleteCertificate($id)) {
            echo json_encode(['message' => 'Certificate deleted']);
        } else {
            echo json_encode(['error' => 'Delete failed']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}