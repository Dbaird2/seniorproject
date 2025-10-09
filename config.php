<?php
use Dotenv\Dotenv;
require __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$db_host = envx('DB_HOST');
$db_port = envx('DB_PORT', '5432');
$db_name = envx('DB_NAME');
$db_user = envx('DB_USER');
$db_pass = envx('DB_PASS');
if (!$db_host || !$db_name || !$db_user || !$db_port) {
    error_log('DB config missing: host/port/name/user not set');
    http_response_code(500);
    exit('Server configuration error.');
}
/*
$db_pass = $_ENV['DB_PASS'] ?? NULL;
 */

try {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 43200);
        session_set_cookie_params(43200);
        session_start();
        $_SESSION['app_pass'] = $_ENV['APP_PASS'] ?? NULL;
    }
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, [ 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
        ,]);
    // Set up session only once
} catch (PDOException $e) {
    error_log($e->getMessage());
} 
function check_auth($level = 'low') {
    $levels = [
        'low' => ['user', 'custodian', 'admin', 'management'],
        'medium' => ['custodian', 'admin', 'management'],
        'high' => ['admin']
    ];
    if (!isset($_SESSION['role']) || $_SESSION['role'] === '') {
        header('Location: https://dataworks-7b7x.onrender.com/auth/login.php');
        exit;
    }
    $user_role = $_SESSION['role'];
    if (!in_array($user_role, $levels[$level] ?? [])) {
        header('Location: https://dataworks-7b7x.onrender.com/index.php');
        exit;
    }
    return true;
}
?>
