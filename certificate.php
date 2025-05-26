<?php
require_once __DIR__ . '/functions/certificateFunctions.php';

header("Content-Type: application/json");

$enrollmentIid = isset($_GET['certificateid']) ?$_GET['certificateid']: null;

if(!$enrollmentIid){
    echo json_encode(['error'=>'enrollment id is required']);
}