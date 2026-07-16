<?php
include_once("../config.php");

header('Content-Type: application/json');

/*
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
    */

echo json_encode([
    "status" => "success",
    "message" => "API is working!"
]);
