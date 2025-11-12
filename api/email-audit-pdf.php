<?php
// audit_email_pdf.php
declare(strict_types=1);

header('Content-Type: application/pdf');

include_once __DIR__ . '/../../config.php';

// If this file lives in /audit/audit_history/, vendor is likely TWO levels up:
require_once __DIR__ . '/../../vendor/autoload.php';

check_auth();

use Mpdf\Mpdf;

$dept_id  = $_GET['dept_id']  ?? '';
$audit_id = $_GET['audit_id'] ?? '';
if ($dept_id === '' || $audit_id === '') {
    http_response_code(400);
    exit('Missing dept_id or audit_id');
}

// --- Fetch data ---
$select_query = "
  SELECT * 
  FROM audit_history 
  WHERE dept_id = :dept_id AND audit_id = :audit_id 
  ORDER BY finished_at DESC
";
$stmt = $dbh->prepare($select_query);
$stmt->execute([
    ':dept_id'  => $dept_id,
    ':audit_id' => $audit_id,
]);
$audit_details = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$audit_details) {
    http_response_code(404);
    exit('No audit found');
}

$audit_data = json_decode((string)($audit_details['audit_data'] ?? '[]'), true) ?: [];
$data = [];

foreach ($audit_data as $row) {
    if (!empty($row['Tag Number']) && $row['Tag Number'] !== 'Tag Number') {
        $data[] = [
            'Unit'               => $row['Unit'] ?? '',
            'Tag Number'         => $row['Tag Number'],
            'Descr'              => $row['Descr'] ?? '',
            'Serial ID'          => $row['Serial ID'] ?? '',
            'Location'           => $row['Location'] ?? '',
            'VIN'                => $row['VIN'] ?? '',
            'Custodian'          => $row['Custodian'] ?? '',
            'Dept'               => $row['Dept'] ?? '',
            'PO No.'             => $row['PO No.'] ?? '',
            'Acq Date'           => $row['Acq Date'] ?? '',
            'COST Total Cost'    => $row['COST Total Cost'] ?? '',
            'Tag Status'         => $row['Tag Status'] ?? '',
            'Found Room Number'  => $row['Found Room Number'] ?? '',
            'Found Building Name'=> $row['Found Building Name'] ?? '',
            'Found Note'         => $row['Found Note'] ?? '',
            'Found Timestamp'    => $row['Found Timestamp'] ?? '',
        ];
    }
}

// --- Build HTML (no echo to browser) ---
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Audit Details - Department <?= htmlspecialchars((string)$audit_details['dept_id']) ?></title>
<style>
/* keep your CSS — trimmed for brevity */
body { font-family: Arial, sans-serif; }
table { width:100%; border-collapse:collapse; }
th, td { border:1px solid #e5e7eb; padding:8px; font-size:12px; }
thead { background:#0ea5e9; color:#fff; }
</style>
</head>
<body>
<table>
  <thead>
    <tr>
      <th>Department</th><th>Auditor</th><th>Audited With</th><th>Timestamp</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?= htmlspecialchars((string)$audit_details['dept_id']) ?></td>
      <td><?= htmlspecialchars((string)($audit_details['auditor'] ?? '')) ?></td>
      <td><?= htmlspecialchars((string)($audit_details['audited_with'] ?? '')) ?></td>
      <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime((string)$audit_details['finished_at']))) ?></td>
    </tr>
  </tbody>
</table>

<h3>Assets</h3>
<table>
  <thead>
    <tr>
      <th>Unit</th><th>Tag Number</th><th>Description</th><th>Serial ID</th>
      <th>Location</th><th>VIN</th><th>Custodian</th><th>Dept ID</th>
      <th>PO No.</th><th>Acq Date</th><th>Cost</th><th>Status</th>
      <th>Found Room #</th><th>Found Building</th><th>Note</th><th>Found Timestamp</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $i => $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['Unit']) ?></td>
        <td><?= htmlspecialchars($row['Tag Number']) ?></td>
        <td><?= htmlspecialchars($row['Descr']) ?></td>
        <td><?= htmlspecialchars($row['Serial ID']) ?></td>
        <td><?= htmlspecialchars($row['Location']) ?></td>
        <td><?= htmlspecialchars($row['VIN']) ?></td>
        <td><?= htmlspecialchars(str_replace('"','',$row['Custodian'])) ?></td>
        <td><?= htmlspecialchars($row['Dept']) ?></td>
        <td><?= htmlspecialchars($row['PO No.']) ?></td>
        <td><?= htmlspecialchars($row['Acq Date']) ?></td>
        <td><?= htmlspecialchars($row['COST Total Cost']) ?></td>
        <td><?= htmlspecialchars($row['Tag Status']) ?></td>
        <td><?= htmlspecialchars($row['Found Room Number']) ?></td>
        <td><?= htmlspecialchars($row['Found Building Name']) ?></td>
        <td><?= htmlspecialchars($row['Found Note']) ?></td>
        <td><?= htmlspecialchars($row['Found Timestamp']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>
<?php
$html = ob_get_clean();

// --- Render PDF and output raw bytes ---
$mpdf = new Mpdf([
    'mode'         => 'utf-8',
    'format'       => 'A4-L',
    'margin_left'  => 10,
    'margin_right' => 10,
    'margin_top'   => 10,
    'tempDir'      => sys_get_temp_dir(), // helpful on PaaS/Windows
    'default_font' => 'dejavusans',
]);
$mpdf->SetTitle('Audit Details');
$mpdf->WriteHTML($html);

// IMPORTANT: return bytes only (no extra echo/whitespace)
echo $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
