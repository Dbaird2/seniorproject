<?php
include_once(__DIR__ . "/../../config.php");
require_once __DIR__ . '/../../vendor/autoload.php';

check_auth();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$auditor = $_GET['auditor'] ?? '';
$dept_id = $_GET['dept_id'] ?? '';
$audit_id = $_GET['audit_id'] ?? '';

$select_query = "SELECT * FROM audit_history WHERE dept_id = :dept_id AND auditor = :auditor AND audit_id = :audit_id ORDER BY finished_at DESC";
$stmt = $dbh->prepare($select_query);
$stmt->execute([':dept_id' => $dept_id, ':auditor' => $auditor, ':audit_id' => $audit_id]);
$audit_details = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$audit_details) {
    exit;
}

$audit_data = json_decode((string) $audit_details['audit_data'], true);
try {
    $index = 0;
    foreach ($audit_data as $row) {

        if ($row['Tag Number'] !== '' && $row['Tag Number'] !== NULL && $row['Tag Number'] !== 'Tag Number') {
            $data[$index]['Unit'] = $row['Unit'] ?? '';
            $data[$index]['Tag Number'] = $row['Tag Number'];
            $data[$index]['Descr'] = $row['Descr'] ?? '';
            $data[$index]['Serial ID'] = $row['Serial ID'] ?? '';
            $data[$index]['Location'] = $row['Location'] ?? '';
            $data[$index]['VIN'] = $row['VIN'] ?? '';
            $data[$index]['Custodian'] = $row['Custodian'] ?? '';
            $data[$index]['Dept'] = $row['Dept'] ?? '';
            $data[$index]['PO No.'] = $row['PO No.'] ?? '';
            $data[$index]['Acq Date'] = $row['Acq Date'] ?? '';
            $data[$index]['COST Total Cost'] = $row['COST Total Cost'] ?? '';
            $data[$index]['Tag Status'] = $row['Tag Status'] ?? '';
            $data[$index]['Found Room Tag'] = $row['Found Room Tag'] ?? '';
            $data[$index]['Found Note'] = $row['Found Note'] ?? '';
            $data[$index++]['Found Timestamp'] = $row['Found Timestamp'] ?? '';
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}



ob_start(); 

    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="audit-details.css">
</head>
<body class="is-audit-details">
    <table class="header-table" id="header-table">
        <thead>
            <tr class="odd">
                <th>Department</th>
                <th>Auditor</th>
                <th>Audit ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i= 0;
            $color = ($i++ % 2 === 0) ? 'even' : 'odd';
            echo "<tr class='$color'>";
            echo "<td>" . $audit_details['dept_id'] . "</td>";
            echo "<td>" . $audit_details['auditor'] . "</td>";
            echo "<td>" . date('Y-m-d H:i:s', strtotime((string) $audit_details['finished_at'])) . "</td>";
            echo "</tr>";
            
                        ?>
        </tbody>
    </table>
    <section class="middle">
    
<div class="audit-data">
    <h3>Department <?php echo htmlspecialchars((string) $audit_details['dept_id']); ?> Assets</h3>
    <table>
        <thead>
            <tr>
                    <th class='odd'>Unit</th>
                    <th class='odd'>Tag Number</th>
                    <th class='odd'>Description</th>
                    <th class='odd'>Serial ID</th>
                    <th class='odd'>Location</th>
                    <th class='odd'>VIN</th>
                    <th class='odd'>Custodian</th>
                    <th class='odd'>Dept ID</th>
                    <th class='odd'>PO No.</th>
                    <th class='odd'>Acq Date</th>
                    <th class='odd'>Cost</th>
                    <th class='odd'>Status</th>
                    <th class='odd'>Found Room Tag</th>
                    <th class='odd'>Note</th>
                    <th class='odd'>Found Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php  $j = 1;
            foreach ($data as $index=>$row) {
                echo "<tr>";
                $color = ($index % 2 == 0) ? 'even' : 'odd'; 
                if ($row['Tag Status'] === 'Found') {
                    echo "<td class='$color' style='color:green;font-weight:700;'>". htmlspecialchars($row['Unit']) ."</td>";
                    echo "<td class='$color' style='color:green;font-weight:700;'>". htmlspecialchars($row['Tag Number']). "</td>";
                    echo "<td class='$color' style='color:green;font-weight:700;'>". htmlspecialchars($row['Descr']). "</td>";
                } else if ($row['Tag Status'] === 'Extra') {
                    echo "<td class='$color' style='color:blue;font-weight:700;'>". htmlspecialchars($row['Unit']) ."</td>";
                    echo "<td class='$color' style='color:blue;font-weight:700;'>". htmlspecialchars($row['Tag Number']). "</td>";
                    echo "<td class='$color' style='color:blue;font-weight:700;'>". htmlspecialchars($row['Descr']). "</td>";
                }
                    echo "<td class='$color' >". htmlspecialchars($row['Serial ID']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Location']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['VIN']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Custodian']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Dept']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['PO No.']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Acq Date']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['COST Total Cost']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Tag Status']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Found Room Tag']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Found Note']). "</td>";
                    echo "<td class='$color' >". htmlspecialchars($row['Found Timestamp']). "</td>";
                    echo "</tr>";
            }
?>
                </tbody>
            </table>
        </div>
    </section>
</html>
<?php 
            $html = ob_get_clean();
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Audit Details - ' . htmlspecialchars((string) $audit_details['dept_id']));
            $mpdf->SetAuthor(htmlspecialchars((string) $audit_details['auditor']));
            $mpdf->Output('audit-details-'.htmlspecialchars((string) $audit_details['dept_id']).'.pdf', 'D');


