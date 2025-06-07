<?php
include __DIR__ . '/middleware/cors.php';
require_once __DIR__ . '/functions/inquiryFunctions.php';
include __DIR__ . '/middleware/auth.php';


header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $user = requireAuth();

        $page = (isset($_GET['page']) && $_GET['page']!='') ? (int)$_GET['page'] : 1;
        $pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;

        // Extract filters from query parameters
        $filters = [];
        foreach ($_GET as $key => $value) {
            if (in_array($key, ['name', 'district', 'state', 'mobile', 'email', 'education', 'course', 'message']) && $value !='') {
                $filters[$key] = $value;
            }
        }

        

        $response = getAllInquiries($page, $pageSize, $filters);
        echo json_encode($response);
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON input.');
            }

            $requiredFields = ['name', 'district', 'state', 'mobile', 'email', 'education', 'course', 'message'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new InvalidArgumentException("Field '{$field}' is required.");
                }
            }

            $insertedId = createInquiry($data);
            echo json_encode(['success' => true, 'message' => 'Inquiry submitted', 'id' => $insertedId]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? $data['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Inquiry ID is required']);
            exit;
        }

        unset($data['id']);

        if (updateInquiry($id, $data)) {
            echo json_encode(['success' => true, 'message' => 'Inquiry updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? $data['id'] : null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Inquiry ID is required']);
            exit;
        }

        if (deleteInquiry($id)) {
            echo json_encode(['success' => true, 'message' => 'Inquiry deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}
