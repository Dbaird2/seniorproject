<?php 
require_once "../../../config.php";

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
if (isset($_POST)) {
    $dept_id = $_GET['dept_id'];
    $audit_id = (int)$_GET['audit_id'];
    $audit_type = match ($audit_id) {
        1 => 'cust',
        2 => 'cust',
        3 => 'mgmt',
        4 => 'mgmt',
        5 => 'SPA',
        6 => 'SPA',
        7 => 'FDN',
        8 => 'FDN'
    };
    try {
        $select_q = "SELECT auditor, audit_data FROM audit_history WHERE dept_id = :dept_id AND audit_id = :audit_id";
        $select_stmt = $dbh->prepare($select_q);
        $select_stmt->execute([":dept_id"=>$dept_id,":audit_id"=>$audit_id]);
        $data = $select_stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting info: " . $e->getMessage());
        exit;
    }
    $audit_data = json_decode($data['audit_data'], true);
    unset($_SESSION['data']);
    $index = 0;
    foreach ($audit_data as $row) {

        if ($row['Tag Number'] !== '' && $row['Tag Number'] !== NULL && $row['Tag Number'] !== 'Tag Number') {
            $_SESSION['data'][$index]['Unit'] = $row['Unit'] ?? '';
            $_SESSION['data'][$index]['Tag Number'] = $row['Tag Number'];
            $_SESSION['data'][$index]['Descr'] = $row['Descr'] ?? '';
            $_SESSION['data'][$index]['Serial ID'] = $row['Serial ID'] ?? '';
            $_SESSION['data'][$index]['Location'] = $row['Location'] ?? '';
            $_SESSION['data'][$index]['VIN'] = $row['VIN'] ?? '';
            $_SESSION['data'][$index]['Custodian'] = $row['Custodian'] ?? '';
            $_SESSION['data'][$index]['Dept'] = $row['Dept'] ?? '';
            $_SESSION['data'][$index]['PO No.'] = $row['PO No.'] ?? '';
            $_SESSION['data'][$index]['Acq Date'] = $row['Acq Date'] ?? '';
            $_SESSION['data'][$index]['COST Total Cost'] = $row['COST Total Cost'] ?? '';
            $_SESSION['data'][$index]['Tag Status'] = $row['Tag Status'] ?? '';
            $_SESSION['data'][$index]['Found Room Tag'] = $row['Found Room Tag'] ?? '';
            $_SESSION['data'][$index]['Found Note'] = $row['Found Note'] ?? '';
            $_SESSION['data'][$index++]['Found Timestamp'] = $row['Found Timestamp'] ?? '';
            echo "<pre>";
            var_dump($row);
            echo "</pre>";
        }
    }
    unset($_SESSION['info']);
    $_SESSION['info'] = [$index, 1, $dept_id, $audit_type, $dept_id];
    //header("Location: https://dataworks-7b7x.onrender.com/audit/auditing.php");
    //exit;
} 
