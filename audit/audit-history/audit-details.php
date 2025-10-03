<?php
include_once(__DIR__ . "/../../config.php");
require_once __DIR__ . '/../../vendor/autoload.php';
/* THIS IS FOR PDF DOWNLOAD */

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
    <title>Audit Details - Department <?php echo htmlspecialchars((string) $audit_details['dept_id']); ?></title>
    <!-- Added comprehensive CSS styling with light blue and white theme -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .page-wrapper {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 2rem;
            color: #1e293b;
        }

        .header-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(14, 165, 233, 0.1);
            margin-bottom: 2rem;
            border: 1px solid #e0f2fe;
        }

        .header-table thead {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }

        .header-table thead th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: white;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #e0f2fe;
            font-size: 0.95rem;
            color: #334155;
        }

        .header-table tbody tr:last-child td {
            border-bottom: none;
        }

        .middle {
            width: 100%;
        }

        .audit-data {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(14, 165, 233, 0.1);
            border: 1px solid #e0f2fe;
        }

        .audit-data h3 {
            color: #0284c7;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0f2fe;
        }

        .audit-data table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 8px;
        }

        .audit-data thead {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .audit-data thead th {
            padding: 1rem 0.75rem;
            text-align: left;
            font-weight: 600;
            color: white;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .audit-data thead th:last-child {
            border-right: none;
        }

        .audit-data tbody td {
            padding: 0.875rem 0.75rem;
            font-size: 0.9rem;
            color: #334155;
            border-right: 1px solid #f1f5f9;
        }

        .audit-data tbody td:last-child {
            border-right: none;
        }

        .audit-data tbody tr.even td.even {
            background-color: #f8fafc;
        }

        .audit-data tbody tr.odd td.odd {
            background-color: white;
        }

        .audit-data tbody tr:hover td {
            background-color: #e0f2fe !important;
            transition: background-color 0.2s ease;
        }

        /* Status color overrides */
        .audit-data tbody td[style*="color:green"] {
            color: #16a34a !important;
        }

        .audit-data tbody td[style*="color:blue"] {
            color: #0284c7 !important;
        }

        @media print {
            .page-wrapper {
                background: white;
                padding: 0;
            }

            .header-table,
            .audit-data {
                box-shadow: none;
                border: 1px solid #e0f2fe;
            }
        }

        @media (max-width: 768px) {
            .page-wrapper {
                padding: 1rem;
            }

            .audit-data {
                padding: 1rem;
                overflow-x: auto;
            }

            .audit-data table {
                font-size: 0.8rem;
            }

            .audit-data thead th,
            .audit-data tbody td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
    <table class="header-table" id="header-table">
        <thead>
            <tr class="odd">
                <th>Department</th>
                <th>Auditor</th>
                <th>Audited With</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i= 0;
            $color = ($i++ % 2 === 0) ? 'even' : 'odd';
            echo "<tr class='$color'>";
            echo "<td>" . $audit_details['dept_id'] . "</td>";
            echo "<td>" . $audit_details['auditor'] . "</td>";
            echo "<td>" . $audit_details['audited_with'] ?? '' . "</td>";
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
                    <th class='odd'>Found Room Number</th>
                    <th class='odd'>Found Building Name</th>
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
                } else {
                    echo "<td class='$color' style='font-weight:700;'>". htmlspecialchars($row['Unit']) ."</td>";
                    echo "<td class='$color' style='font-weight:700;'>". htmlspecialchars($row['Tag Number']). "</td>";
                    echo "<td class='$color' style='font-weight:700;'>". htmlspecialchars($row['Descr']). "</td>";

                }
                echo "<td class='$color' >". htmlspecialchars($row['Serial ID']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Location']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['VIN']). "</td>";
                echo "<td class='$color' >". htmlspecialchars(str_replace('"', '', $row['Custodian'])). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Dept']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['PO No.']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Acq Date']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['COST Total Cost']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Tag Status']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Found Room Number']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Found Building Name']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Found Note']). "</td>";
                echo "<td class='$color' >". htmlspecialchars($row['Found Timestamp']). "</td>";
                echo "</tr>";
            }
?>
                </tbody>
            </table>
        </div>
    </section>
    </div>
</body>
</html>
<?php
            $html = ob_get_clean();
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L', // Landscape orientation
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 5,
                'margin_footer' => 5
            ]);
            
            // Add PDF-specific CSS to make table more compact
            $pdfStyles = '<style>
                .page-wrapper { padding: 0.5rem; background: white; }
                .header-table { margin-bottom: 1rem; font-size: 9pt; }
                .header-table th, .header-table td { padding: 0.4rem; }
                .audit-data { padding: 0.5rem; }
                .audit-data h3 { font-size: 11pt; margin-bottom: 0.5rem; }
                .audit-data table { font-size: 7pt; }
                .audit-data thead th { padding: 0.3rem 0.2rem; font-size: 7pt; }
                .audit-data tbody td { padding: 0.3rem 0.2rem; }
            </style>';
            
            $mpdf->WriteHTML($pdfStyles . $html);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Audit Details - ' . htmlspecialchars((string) $audit_details['dept_id']));
            $mpdf->SetAuthor(htmlspecialchars((string) $audit_details['auditor']));
            $mpdf->Output('audit-details-'.htmlspecialchars((string) $audit_details['dept_id']).'.pdf', 'D');

