<?php
require_once __DIR__ . '/../config/db.php';

// Get all inquiries (with pagination)
function getAllInquiries($page = 1, $pageSize = 10, $filters = []) {
    $pdo = getDbConnection();

    $offset = ($page - 1) * $pageSize;

    // Start building the SQL query
    $sql = "SELECT * FROM inquiries";
    $conditions = [];
    $params = [];

    // Check for filters and build conditions
    foreach ($filters as $key => $value) {
        if (in_array($key, ['name', 'district', 'state', 'mobile', 'email', 'education', 'course', 'message'])) {
            $conditions[] = "$key LIKE :$key";
            $params[":$key"] = "%$value%"; // Use LIKE for partial matches
        }
    }

    // Append conditions to the SQL query if any
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    // Bind additional parameters for filters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = $pdo->query("SELECT COUNT(*) FROM inquiries" . (!empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : ""))->fetchColumn();

    return [
        'data' => $inquiries,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'pageSize' => $pageSize
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
