<?php
include __DIR__ . '/middleware/cors.php';




require_once __DIR__ . '/functions/certificateFunctions.php';


header("Content-Type: application/json");

$enrollmentNo = isset($_GET['e']) ?$_GET['e']: null;

if(!$enrollmentNo){
    echo json_encode(['error'=>'enrollment no / roll no is required']);
    exit;
}

$result = getCertificateById($enrollmentNo);
if(!$result){
    http_response_code(404);
    echo json_encode(["error"=>"No record found for enrollment number / Roll No : {$enrollmentNo}"]);
    exit;
}
echo json_encode(["student"=>$result]);