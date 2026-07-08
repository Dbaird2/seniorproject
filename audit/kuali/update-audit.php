<?php
include_once('../../config.php');
foreach ($_SESSION['data'] as $index => $asset) {
    if (in_array($asset['Tag Status'], ['Found', 'Extra'])) {
        continue;
    }
    $audit_id = $_SESSION['info'][5];
    $dept_id = $_SESSION['info'][4];
    $select = "SELECT asset_notes FROM asset_info WHERE asset_tag = :tag AND asset_notes ILIKE '%CHCKD%'";
    $stmt = $dbh->prepare($select);
    $stmt->execute([':tag' => $asset['Tag Number']]);
    $notes = $stmt->fetchColumn();
    if (isset($notes['asset_notes'])) {
        $_SESSION['data'][$index]['Found Note'] .= ' Check-Out {' . $notes . '} ';
        $_SESSION['data'][$index]['Tag Status'] = 'Found';
        $_SESSION['data'][$index]['Found Room Number'] = 'CHCKD';
    } else {
        $select = 'SELECT asset_tag, note, dept_id FROM audited_asset WHERE asset_tag = :tag AND audit_id = :id AND dept_id != :dept_id';
        $stmt = $dbh->prepare($select);
        $stmt->execute([':tag' => $asset['Tag Number'], ':id' => $audit_id, ':dept_id' => $dept_id]);
        $result = $stmt->fetch();
        if ($result) {
            $_SESSION['data'][$index]['Found Note'] .= ' Found at ' . $result['dept_id'] . ' ';
            $_SESSION['data'][$index]['Tag Status'] = 'Found';
        }
    }
    $notes = $result = '';
}
echo json_encode(['status' => 'Ok']);
exit;
