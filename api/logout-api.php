<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

function sendJson(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');

    sendJson(405, [
        'success' => false,
        'error' => 'Method Not Allowed'
    ]);
}

try {
    $currentUser = check_api_auth($dbh, 'low');

    $stmt = $dbh->prepare(
        'UPDATE user_table
         SET
            api_token = NULL,
            token_expires = NULL
         WHERE id = ?'
    );

    $stmt->execute([$currentUser['id']]);

    sendJson(200, [
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
} catch (PDOException $e) {
    error_log($e->__toString());

    sendJson(500, [
        'success' => false,
        'error' => 'Database error'
    ]);
} catch (Throwable $e) {
    error_log($e->__toString());

    sendJson(500, [
        'success' => false,
        'error' => 'Server error'
    ]);
}
