<?php
include __DIR__ . '/middleware/cors.php';
include __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/functions/certificateFunctions.php';

header("Content-Type: application/json");


// Authenticate the user
$user = requireAuth();


$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$enrollmentIid = null;

if (preg_match('/\/certificates\/(\d+)/', $path, $matches)) {
    $id = $matches[1];
}

switch ($method) {
    case 'GET':
        // Extract filters from $_GET, excluding page and pageSize
        $allowedFilters = [
            'name', 'fatherName', 'motherName', 'dateOfBirth',
            'aadharCardNumber', 'enrolmentNumber',  'courseName',
             'courseDuration', 'totalObtainedMarks',
            'overallPercentage', 'grade', 'finalResult', 'certificateIssueDate',
            'trainingCentre'
        ];
    
        $filters = [];
        foreach ($allowedFilters as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $filters[$key] = $_GET[$key];
            }
        }
    
        // Pagination parameters
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['pageSize']) && is_numeric($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
    
        // Get data with filters and pagination
        $response = getAllCertificates($filters, $page, $pageSize);
    
        header('Content-Type: application/json');
        echo json_encode($response);
        break;
    

    case 'POST':
        header('Content-Type: application/json');   
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON input.');
            }

            $insertedId = createCertificate($data);
            echo json_encode(['success' => true, 'message' => 'Certificate created', 'id' => $insertedId]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ]);
        }
        break;


    case 'PUT':
        $id=null;
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? $data['id'] : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Certificate ID required']);
            exit;
        }
        // removing id from data to avoid updating it
        unset($data['id']);
        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'No data provided for update']);
            exit;
        }
        if (updateCertificate($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Certificate updated']);
        } else {
            echo json_encode(['error' => 'Update failed']);
        }
        break;
    case 'DELETE':
        $id=null;
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? $data['id'] : null;
        
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