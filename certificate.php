<?php
require_once __DIR__ . '/functions/certificateFunctions.php';

include __DIR__ . '/middleware/cors.php';

header("Content-Type: application/json");

$enrollmentNo = isset($_GET['e']) ?$_GET['e']: null;

if(!$enrollmentNo){
    echo json_encode(['error'=>'enrollment id is required']);
    exit;
}

$result = getCertificateById($enrollmentNo);
if(!$result){
    http_response_code(404);
    echo json_encode(["error"=>"No record found for enrollment number : {$enrollmentNo}"]);
    exit;
}
echo json_encode(["student"=>$result]);