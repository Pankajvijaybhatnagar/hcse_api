<?php
require_once __DIR__ . '/../config/db.php';


function createCertificate($data) {
    $pdo = getDbConnection();

    // Required fields
    $requiredFields = [
        'name',
        'fatherName',
        'dateOfBirth',
        'aadharCardNumber',
        'enrolmentNumber',
        'courseName'
    ];

    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new InvalidArgumentException("Field '$field' is required.");
        }
    }

    // Check for duplicates using enrolmentNumber only
    $stmt = $pdo->prepare("SELECT id FROM certificates WHERE enrolmentNumber = :enrolment");
    $stmt->execute([
        ':enrolment' => $data['enrolmentNumber']
    ]);

    if ($stmt->fetch()) {
        // Duplicate entry exists
        http_response_code(402);
        throw new Exception('Duplicate: Enrolment Number');
    }

    // All possible fields in the table (updated)
    $allFields = [
        'name', 'fatherName', 'motherName', 'dateOfBirth', 'aadharCardNumber', 'enrolmentNumber',
        'rollNo', 'courseName', 'courseDuration', 'totalObtainedMarks', 'overallPercentage', 
        'grade', 'finalResult', 'certificateIssueDate', 'trainingCentre', 'avatar'
    ];

    $insertFields = [];
    $insertValues = [];
    $insertParams = [];

    foreach ($allFields as $field) {
        $insertFields[] = $field;
        if (isset($data[$field]) && $data[$field] !== '') {
            $insertValues[] = ":$field";
            $insertParams[$field] = $data[$field];
        } else {
            $insertValues[] = "NULL";
        }
    }

    $sql = "INSERT INTO certificates (" . implode(', ', $insertFields) . ")
            VALUES (" . implode(', ', $insertValues) . ")";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($insertParams);

    return $pdo->lastInsertId();
}






function getAllCertificates($filters = [], $page = 1, $pageSize = 10) {
    $pdo = getDbConnection();

    $whereClauses = [];
    $params = [];

    // Add dynamic filters
    foreach ($filters as $key => $value) {
        // Simple filter only for non-empty values to avoid SQL errors
        if (!empty($value)) {
            // Use named placeholders for security
            $whereClauses[] = "$key LIKE :$key";
            $params[$key] = "%$value%"; // using LIKE for partial matching
        }
    }

    // Build WHERE clause
    $where = '';
    if (count($whereClauses) > 0) {
        $where = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Calculate LIMIT and OFFSET for pagination
    $offset = ($page - 1) * $pageSize;
    $limitClause = "LIMIT :limit OFFSET :offset";

    // Prepare the SQL query with filters and pagination
    $sql = "SELECT * FROM certificates
            $where
            ORDER BY id DESC
            $limitClause";

    $stmt = $pdo->prepare($sql);

    // Bind filter values
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }

    // Bind limit and offset as integers
    $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    

    // Get total count for pagination info (without limit)
    $countSql = "SELECT COUNT(*) FROM certificates $where";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue(":$key", $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalRecords = (int)$countStmt->fetchColumn();

    return [
        'data' => $results,
        'pagination' => [
            'currentPage' => (int)$page,
            'pageSize' => (int)$pageSize,
            'totalRecords' => $totalRecords,
            'totalPages' => ceil($totalRecords / $pageSize)
        ]
    ];
}


function getCertificateById($enrollmentIid) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE enrolmentNumber = ? OR rollNo = ?");
    $stmt->execute([$enrollmentIid,$enrollmentIid]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateCertificate($id, $data) {
    $pdo = getDbConnection();

    // All allowed fields (same as in createCertificate)
    $allFields = [
        'name', 'fatherName', 'motherName', 'dateOfBirth', 'aadharCardNumber', 'enrolmentNumber',
        'rollNo', 'courseName', 'courseDuration', 'totalObtainedMarks', 'overallPercentage', 
        'grade', 'finalResult', 'certificateIssueDate', 'trainingCentre', 'avatar'
    ];

    $setClauses = [];
    $params = ['id' => $id];

    foreach ($allFields as $field) {
        if (isset($data[$field]) && $data[$field] !== '') {
            $setClauses[] = "$field = :$field";
            $params[$field] = $data[$field];
        }
    }

    if (empty($setClauses)) {
        throw new InvalidArgumentException("No valid fields provided for update.");
    }

    $sql = "UPDATE certificates SET " . implode(', ', $setClauses) . " WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}


function deleteCertificate($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
    return $stmt->execute([$id]);
}