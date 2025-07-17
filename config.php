<?php
use Dotenv\Dotenv;
require __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/etc/secrets/DB_HOST')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
$db_host = $_ENV['DB_HOST'] ?? NULL;
$db_port = $_ENV['DB_PORT'] ?? NULL;
$db_name = $_ENV['DB_NAME'] ?? NULL;
$db_user = $_ENV['DB_USER'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;

try {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 43200);
        session_set_cookie_params(43200);
        session_start();
        $_SESSION['app_pass'] = $_ENV['APP_PASS'] ?? NULL;
    }
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, array());
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
