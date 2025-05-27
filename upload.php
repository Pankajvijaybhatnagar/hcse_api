<?php
include __DIR__ . '/middleware/cors.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

// Sanitize filename
function sanitize_filename($filename) {
    // Remove path information and dots from filename
    $filename = basename($filename);
    // Remove unwanted characters
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
    // Prevent multiple dots
    $filename = preg_replace('/\.+/', '.', $filename);
    return $filename;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$originalName = $_FILES['file']['name'];
$sanitized = sanitize_filename($originalName);

// Ensure unique filename
$ext = pathinfo($sanitized, PATHINFO_EXTENSION);
$name = pathinfo($sanitized, PATHINFO_FILENAME);
$newFilename = $name . '_' . uniqid() . ($ext ? '.' . $ext : '');

$targetPath = $uploadDir . $newFilename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    echo json_encode([
        'success' => true,
        'filename' => $newFilename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to upload file.']);
}
?>