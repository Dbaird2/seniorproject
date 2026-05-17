<?php
header('Content-Type: application/json');
require_once '../config.php';
try {
    $limit  = max(1, min(50, (int)($_GET['limit'] ?? 25)));
    $q      = trim($_GET['q']     ?? '');
    $status = trim($_GET['status'] ?? '');
    $type   = trim($_GET['type']  ?? '');
    $before = isset($_GET['before_id']) ? (int)$_GET['before_id'] : null;

    $sql = "SELECT id, email, type, date_added, info, ticket_status
        FROM ticket_table WHERE 1=1";
    $bind = [];

    if ($q !== '') {
        $sql .= " AND (email ILIKE ? OR info ILIKE ?)";
        $bind[] = "%$q%";
        $bind[] = "%$q%";
    }
    if ($status !== '') {
        $sql .= " AND ticket_status = ?";
        $bind[] = $status;
    }
    if ($type !== '') {
        $sql .= " AND (type = ?)";
        $bind[] = $type;
    }
    if ($before !== null) {
        $sql .= " AND id < ?";
        $bind[] = $before;
    }

    $sql .= " ORDER BY date_added DESC, id DESC LIMIT ?";
    $bind[] = $limit;
    
    $query_repo->fetchAll($sql, ...$bind);
    echo json_encode(['tickets' => $query_repo->fetchAll($sql, ...$bind)]);
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
} catch (Exception $e) {
    error_log($e->getMessage());
}
