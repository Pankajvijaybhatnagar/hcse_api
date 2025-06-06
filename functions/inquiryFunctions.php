<?php
require_once __DIR__ . '/../config/db.php';

// Get all inquiries (with pagination)
function getAllInquiries($page = 1, $pageSize = 10, $filters = []) {
    $pdo = getDbConnection();

    $whereClauses = [];
    $params = [];

    // Add dynamic filters
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
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
    $sql = "SELECT * FROM inquiries
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
    $countSql = "SELECT COUNT(*) FROM inquiries $where";
    $countStmt = $pdo->prepare($countSql);
    
    // Bind filter values for count query
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



// Create a new inquiry
function createInquiry($data) {
    $pdo = getDbConnection();

    $sql = "INSERT INTO inquiries (name, district, state, mobile, email, education, course, message, remark)
            VALUES (:name, :district, :state, :mobile, :email, :education, :course, :message, :remark)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $data['name'],
        ':district' => $data['district'],
        ':state' => $data['state'],
        ':mobile' => $data['mobile'],
        ':email' => $data['email'],
        ':education' => $data['education'],
        ':course' => $data['course'],
        ':message' => $data['message'],
        ':remark' => isset($data['remark']) ? $data['remark'] : null
    ]);

    return $pdo->lastInsertId();
}

// Update an inquiry
function updateInquiry($id, $data) {
    $pdo = getDbConnection();

    $fields = [];
    $params = [':id' => $id];

    foreach ($data as $key => $value) {
        $fields[] = "`$key` = :$key";
        $params[":$key"] = $value;
    }

    $sql = "UPDATE inquiries SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// Delete an inquiry
function deleteInquiry($id) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
    return $stmt->execute([$id]);
}
