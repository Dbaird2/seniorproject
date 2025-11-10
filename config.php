<?php
use Dotenv\Dotenv;
require __DIR__ . '/vendor/autoload.php';
/*
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
 */
$db_host = $_ENV['DB_HOST'] ?? NULL;
$db_port = $_ENV['DB_PORT'] ?? NULL;
$db_name = $_ENV['DB_NAME'] ?? NULL;
$db_user = $_ENV['DB_USER'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;
$db_pass = $_ENV['DB_PASS'] ?? NULL;

try {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', 43200);
        ini_set('display_errors', '0');
        ini_set('error_logs', '1');
        ini_set('display_startup_errors', '0');
        ini_set('error_reporting', (string)E_ALL);
        ini_set('error_log','/var/log/php/app-error.log');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cookie_secure', '1');
        ini_set('session.use_trans_sid', '0');
            ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '1000');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '700');
        ini_set('max_input_time', '120');
        ini_set('upload_max_filesize', '30M');
        ini_set('default_charset', 'UTF-8');
        session_set_cookie_params(43200);
        session_start();
        $_SESSION['app_pass'] = $_ENV['APP_PASS'] ?? NULL;
    }
    $dbh = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass, array());
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

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
