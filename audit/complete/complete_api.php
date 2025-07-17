<?php
header("Access-Control-Allow-Headers: Content-Type");
include_once("../../config.php");

if (!isset($_SESSION['role'])) {
    header("Location: https://dataworks-7b7x.onrender.com/auth/login.php");
    exit;
} 
$result = file_get_contents("php://input");
$content_type = $_SERVER["CONTENT_TYPE"] ?? '';

$data = json_decode($result, true);

echo json_encode(['status'=>'success']);
exit;
