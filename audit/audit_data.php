<?php
//header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
$input = file_get_contents('php://input');
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';


$data = json_decode($input, true);



$_SESSION['audit_data'] = $data;

header("Location: https://dataworks-7b7x.onrender.come/audit/db_audit.php");
echo json_encode(['status' => 'success']);

exit;
?>
