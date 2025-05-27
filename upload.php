<?php
include __DIR__ . '/middleware/cors.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

// Only allow media files (images, audio, video)
$allowedMimeTypes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml',
    'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3', 'audio/x-wav', 'audio/x-m4a',
    'video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/webm', 'video/ogg'
];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Only media files are allowed.']);
    exit;
}

// Prevent dangerous extensions
$forbiddenExtensions = ['php', 'js', 'exe', 'sh', 'bat', 'pl', 'py', 'cgi', 'html', 'htm', 'phtml'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (in_array($ext, $forbiddenExtensions)) {
    http_response_code(400);
    echo json_encode(['error' => 'File type not allowed.']);
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