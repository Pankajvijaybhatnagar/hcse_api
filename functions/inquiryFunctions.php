<?php
require_once __DIR__ . '/../config/db.php';

// Get all inquiries (with pagination)
function getAllInquiries($page = 1, $pageSize = 10) {
    $pdo = getDbConnection();

    $offset = ($page - 1) * $pageSize;
    $stmt = $pdo->prepare("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();

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
