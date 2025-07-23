<?php
//header("Access-Control-Allow-Origin: http://localhost:3000");
try {
    header("Access-Control-Allow-Headers: Content-Type");
    require_once "../../config.php";

    check_auth('high');

    $result = json_decode(file_get_contents("php://input"), true);
    $content_type = $_SERVER["CONTENT_TYPE"] ?? '';
    if (!is_string($result)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input format']);
        exit;
    }
    $audit_data = $_SESSION['data'];

    [$tags, $notes, $times, $rooms, $dept] = explode("|", $result);

    $tags = explode('`', $tags);
    $notes = explode('`', $notes);
    $times = explode('`', $times);
    $rooms = explode('`', $rooms);
    for ($i = 0; $i < count($tags); $i++) {
        $found_assets[] = [
            'Asset Tag' => $tags[$i] ?? '',
            'Asset Note' => $notes[$i] ?? '',
            'Time Scanned' => $times[$i] ?? '',
            'Found Room' => $rooms[$i] ?? ''
        ];
    }
    $found_assets_json = json_encode($found_assets);
    $audited_asset_json = json_encode($audit_data);
    $auditor = $_SESSION['email'];
    $audit_id = NULL;
    try {
        $check_recent_audits = "SELECT auditor, audit_id FROM audit_history WHERE extract(YEAR from finished_at) = extract(YEAR FROM CURRENT_TIMESTAMP) AND dept_id = :dept_id";
        $check_stmt = $dbh->prepare($check_recent_audits);
        $check_stmt->execute([$dept]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $audit_id = (int)$result['id'] ?? NULL;
        }
        if ($audit_id === NULL) {
            $get_audit_ids = "SELECT audit_id FROM audit_history WHERE dept_id = :dept_id ORDER BY finished_at";
            $check_stmt = $dbh->prepare($get_audit_ids);
            $check_stmt->execute([':dept_id'=>$dept]);
            $id_results = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            // CHECK OLDEST AUDIT_ID
            if (count($id_results) >1) {
                $audit_id = (int)$id_results[0]['audit_id'];
            } else if (count($id_results) > 0) {
                $audit_id = $id_results[0]['audit_id'] == 1 ? 2 : 1;
            } else {
                $audit_id = 1;
            }  
        }
    } catch (PDOException $e ) {
        echo json_encode(['status'=>'failed', 'Error on select'=>$e->getMessage()]);
    }
    try {
        $insert_q = "INSERT INTO audit_history (dept_id, audit_id, auditor, audit_data, found_data) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $dbh->prepare($insert_q);
        $insert_stmt->execute([$dept, $audit_id, $auditor, $audited_asset_json, $found_assets_json]);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'failed','Error on Insert'=>$e->getMessage(),]);
    }
} catch (Exception $e) {
    echo json_encode(['status'=>'failed', 'Error'=>$e->getMessage()]);
} 

echo json_encode([
  'status' => 'success'
]);

exit;
