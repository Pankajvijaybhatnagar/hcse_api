<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDbConnection();

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'user',
        token VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);

    echo json_encode(['success' => true, 'message' => "Table 'users' created successfully."]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}



try {
    $pdo = getDbConnection();

    $sql = "
        CREATE TABLE IF NOT EXISTS certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            fatherName VARCHAR(255) NOT NULL,
            motherName VARCHAR(255) NOT NULL,
            dateOfBirth DATE NOT NULL,
            aadharCardNumber VARCHAR(20) NOT NULL UNIQUE,
            enrolmentNumber VARCHAR(50) NOT NULL UNIQUE,
            enrolmentDate DATE NOT NULL,
            courseName VARCHAR(255) NOT NULL,
            courseStatus VARCHAR(100) NOT NULL,
            academicDivision VARCHAR(100) NOT NULL,
            courseDuration VARCHAR(50) NOT NULL,
            totalObtainedMarks VARCHAR(50),
            overallPercentage VARCHAR(10),
            grade VARCHAR(10),
            finalResult VARCHAR(100),
            certificateIssueDate DATE,
            trainingCentre VARCHAR(255),
            avatar VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);

    echo json_encode(['success' => true, 'message' => '<br>certificates table created successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}