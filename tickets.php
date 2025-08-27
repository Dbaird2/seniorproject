<?php
header('Content-Type: application/json');
require_once '../config.php';

$limit = max(1, min(50, (int)($_GET['limit'] ?? 25)));
$q      = trim($_GET['q']     ?? '');
$status = trim($_GET['status'] ?? '');
$type   = trim($_GET['type']  ?? '');
$before = isset($_GET['before_id']) ? (int)$_GET['before_id'] : null;

$sql = "SELECT id, email, ticket_type, date_added, info, ticket_status
        FROM ticket_table WHERE 1=1";
$bind = [];

if ($q !== '') {
    $sql .= " AND (email ILIKE :q OR info ILIKE :q)";
    $bind[':q'] = "%$q%";
}
if ($status !== '') {
    $sql .= " AND ticket_status = :status";
    $bind[':status'] = $status;
}
if ($type !== '') {
    $sql .= " AND (ticket_type = :type OR \"type\" = :type)";
    $bind[':type'] = $type;
}
if ($before !== null) {
    $sql .= " AND id < :before";
    $bind[':before'] = $before;
}

$sql .= " ORDER BY date_added DESC, id DESC LIMIT :limit";
$stmt = $pdo->prepare($sql);
foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(['tickets' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
exit;

