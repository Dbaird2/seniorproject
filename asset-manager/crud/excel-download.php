<?php
include_once "../../config.php";
require_once "../../vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$email = $_SESSION['email'];
$profile_name = $_GET['profile_name'];
$profile_name = trim($profile_name, "'");

$select_q = "SELECT p.asset_tag, a.asset_name,
    a.room_tag, r.room_loc, b.bldg_name, a.dept_id, a.po, p.asset_note
    FROM user_asset_profile p JOIN asset_info a ON p.asset_tag = a.asset_tag
    JOIN room_table r ON a.room_tag = r.room_tag
    JOIN bldg_table b ON r.bldg_id = b.bldg_id
    WHERE p.profile_name = :profile_name AND p.email = :email";
try {
    $select_stmt = $dbh->prepare($select_q);
    $select_stmt->execute([":profile_name" => $profile_name, ":email" => $email]);
    $result = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    die("Failed to retrieve data.");
}
$spreadsheet = new Spreadsheet();
if ($result) {
    $spreadsheet->getActiveSheet()->fromArray($result, NULL, 'A1');
} else {
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'No Data Found');
}

$writer = new Xlsx($spreadsheet);
$writer->setPreCalculateFormulas(false);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename=' . urlencode($profile_name) . '.xlsx');
$writer->save('php://output');
echo json_encode(['status'=>'Success']);
exit;

