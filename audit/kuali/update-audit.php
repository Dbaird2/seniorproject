<?php
include_once ('../../config.php');
foreach ($_SESSION['data'] as $index => $asset) {
    if (in_array($asset['Tag Status'], ['Found', 'Extra'])) {
        continue;
    }
    $audit_id = $_SESSION['info'][5];
    $select = 'SELECT asset_notes FROM asset_info WHERE asset_tag = :tag';
    $stmt = $dbh->prepare($select);
    $stmt->execute([':tag'=>$asset['Tag Number']]);
    $notes = $stmt->fetchColumn();
    if (!empty($notes)) {
        $regex = '/^D\d{5}\s+/';
        if (preg_match($regex, $notes)) {
            $_SESSION['Found Notes'] .= $notes;
            $_SESSION['Tag Status'] = 'Found';
        }
    }
}
