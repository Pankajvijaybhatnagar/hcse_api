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
            aadharCardNumber VARCHAR(20) NULL,
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



// removing the aadhar unique
try {
    $pdo = getDbConnection();

    // Check if the unique index exists on aadharCardNumber
    $stmt = $pdo->query("
        SHOW INDEX FROM certificates 
        WHERE Non_unique = 0 AND Column_name = 'aadharCardNumber'
    ");
    $index = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($index) {
        $indexName = $index['Key_name'];

        // Drop the unique index
        $sqlDropIndex = "ALTER TABLE certificates DROP INDEX $indexName";
        $pdo->exec($sqlDropIndex);

        // Modify the columns
        

        echo json_encode(['success' => true, 'message' => '<br>UNIQUE constraint on aadharCardNumber removed successfully.']);
    } else {
        echo json_encode(['success' => true, 'message' => '<br>No UNIQUE constraint found on aadharCardNumber.']);
    }
    $sqlModifyColumns = "
        ALTER TABLE certificates
            MODIFY name VARCHAR(255) NULL,
            MODIFY fatherName VARCHAR(255) NULL,
            MODIFY motherName VARCHAR(255) NULL,
            MODIFY dateOfBirth DATE NULL,
            MODIFY aadharCardNumber VARCHAR(20) NULL,
            MODIFY enrolmentNumber VARCHAR(50) NULL,
            MODIFY enrolmentDate DATE NULL,
            MODIFY courseName VARCHAR(255) NULL,
            MODIFY courseStatus VARCHAR(100) NULL,
            MODIFY academicDivision VARCHAR(100) NULL,
            MODIFY courseDuration VARCHAR(50) NULL,
            MODIFY totalObtainedMarks VARCHAR(50) NULL,
            MODIFY overallPercentage VARCHAR(10) NULL,
            MODIFY grade VARCHAR(10) NULL,
            MODIFY finalResult VARCHAR(100) NULL,
            MODIFY certificateIssueDate DATE NULL,
            MODIFY trainingCentre VARCHAR(255) NULL,
            MODIFY avatar VARCHAR(255) NULL
        ";
        $pdo->exec($sqlModifyColumns);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to drop UNIQUE index: ' . $e->getMessage()]);
}


try {
    $pdo = getDbConnection();
    // Drop columns courseStatus, academicDivision, enrolmentDate and add nullable rollNo column
    $sql = "
    ALTER TABLE certificates
        DROP COLUMN courseStatus,
        DROP COLUMN academicDivision,
        DROP COLUMN enrolmentDate,
        ADD COLUMN rollNo VARCHAR(100) NULL
    ";
    $pdo->exec($sql);
    echo json_encode(['success' => true, 'message' => 'Columns dropped and rollNo column added successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update table: ' . $e->getMessage()]);
}


try {
    $pdo = getDbConnection();

    $sql = "
        CREATE TABLE IF NOT EXISTS inquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            district VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            mobile VARCHAR(15) NOT NULL,
            email VARCHAR(100) NOT NULL,
            education VARCHAR(100) NOT NULL,
            course VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            remark TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);

    echo json_encode(['success' => true, 'message' => "Table 'inquiries' created successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}