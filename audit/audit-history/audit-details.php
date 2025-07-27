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

$found_data = json_decode((string) $audit_details['found_data'], true);
$audit_data = json_decode((string) $audit_details['audit_data'], true);
$found_headers = array_keys($found_data[0]);
try {
    if (isset($audit_data[0][0]) ) {
        $audit_headers = $audit_data[0];
        unset($audit_headers['Fund']);
        unset($audit_headers['Manufacturer']);
        unset($audit_headers['Asset ID']);
        unset($audit_headers['Model']);
        unset($audit_data[0]); 
        $audit_data_copy = [];
        foreach ($audit_headers as $header_index => $header) {
            foreach ($audit_data as $row) {
                if ($audit_headers[$header_index] !== 'Manufacturer' && $audit_headers[$header_index] !== 'Fund' && $audit_headers[$header_index] !== 'Asset ID' && $audit_headers[$header_index] !== 'Asset Type' && $audit_headers[$header_index] !== 'Model') {
                    $header_copy[] = $audit_headers[$header_index];
                    $audit_data_copy[$audit_headers[$header_index]][] = $row[$header_index];
                }
            }
        }
        $keys = false;
        $count = count($audit_data[1]);
        $total_count = count($audit_data);
        $header_copy = array_unique($header_copy);
        $counter = count($audit_data_copy['Tag Number']);
        for ($i = 0; $i < $counter; $i++) {
            foreach ($found_data as $found_row) {
                if ($audit_data_copy['Tag Number'][$i] == $found_row['Asset Tag']) {
                    $audit_data_copy['Audit Notes'][$i] = $found_row['Asset Note'];
                    $audit_data_copy['Audit Date'][$i] = date('Y-m-d H:i:s', strtotime((string) $found_row['Time Scanned']));
                    $audit_data_copy['Room Tag'][$i] = $found_row['Found Room'];
                }
            }
        }
        $j = 0;
        foreach ($found_data as $found_row) {
            if (!in_array($found_row['Asset Tag'], $audit_data_copy['Tag Number'])) {
                $audit_data_copy['Tag Number'][($total_count+$j)] = $found_row['Asset Tag'];
                $audit_data_copy['Audit Notes'][($total_count+$j)] = $found_row['Asset Note'] ?? 'No Notes';
                $audit_data_copy['Audit Date'][($total_count+$j)] = date('Y-m-d H:i:s', strtotime((string) $found_row['Time Scanned']));
                $audit_data_copy['Room Tag'][($total_count+$j)] = $found_row['Found Room'];
                $j++;
            }
        }
        $header_copy[] = 'Audit Notes';
        $header_copy[] = 'Audit Date';
        $header_copy[] = 'Room Tag';

    } else {
        $audit_headers = array_keys($audit_data[0]);
        $count = count($audit_data[0]);
        $keys = true;
=        foreach ($audit_headers as $header_index => $header) {
            if ($header === 'po') {
                $headers_copy[$header_index] = 'Purchase Order';
            } elseif ($header === 'dept_id') {
                $headers_copy[$header_index] = 'Department ID';
            } elseif ($header === 'room_loc') {
                $headers_copy[$header_index] = 'Room Number';
            } elseif ($header === 'room_tag') {
                $headers_copy[$header_index] = 'Room Tag';
            } elseif ($header === 'asset_tag') {
                $headers_copy[$header_index] = 'Tag Number';
            } elseif ($header === 'bldg_name') {
                $headers_copy[$header_index] = 'Building Name';
            } elseif ($header === 'asset_name') {
                $headers_copy[$header_index] = 'Asset Name';
            } elseif ($header === 'serial_num') {
                $headers_copy[$header_index] = 'Serial Number';
            } elseif ($header === 'asset_price') {
                $headers_copy[$header_index] = 'Cost';
            }
        }
        $audit_headers[] = 'Audit Notes';
        $audit_headers[] = 'Audit Date';
        $audit_headers[] = 'Room Tag';
        $headers_copy[] = 'Audit Notes';
        $headers_copy[] = 'Audit Date';
        $headers_copy[] = 'Room Tag';
        $audit_data_copy = $audit_data;
        $counter = count($audit_data_copy);

        for ($i = 0; $i < $counter; $i++) {
            foreach ($found_data as $found_index => $found_row) {
                if ($audit_data_copy[$i]['asset_tag'] === $found_row['Asset Tag']) {
                    $audit_data_copy[$i]['Audit Date'] = date('Y-m-d H:i:s', strtotime((string) $found_row['Time Scanned']));
                    $audit_data_copy[$i]['Room Tag'] = $found_row['Found Room'];
                    $audit_data_copy[$i]['Audit Notes'] = $found_row['Asset Note'];
                    $found_data[$found_index]['found'] = true;
                }
            }
        }
        $j = 0;
        foreach ($found_data as $found_row) {
            if (!isset($found_row['found']) || !$found_row['found']) {
                $audit_data_copy[($counter+$j)]['asset_tag'] = $found_row['Asset Tag'];
                $audit_data_copy[($counter+$j)]['Audit Notes'] = $found_row['Asset Note'] ?? 'No Notes';
                $audit_data_copy[($counter+$j)]['Audit Date'] = date('Y-m-d H:i:s', strtotime((string) $found_row['Time Scanned']));
                $audit_data_copy[($counter+$j)]['Room Tag'] = $found_row['Found Room'];
                $j++;
            }
           
        }
        $counter = $counter + $j;
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
    <?php if (!$keys) { ?>
    <section class="middle">
    
<div class="audit-data">
    <h3>Department <?php echo htmlspecialchars((string) $audit_details['dept_id']); ?> Assets</h3>
    <table>
        <thead>
            <tr>
                <?php foreach ($header_copy as $header): ?>
                    <th class='odd'><?php echo htmlspecialchars((string) $header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php  $j = 1;
            $counter = count($audit_data_copy['Tag Number']);
            for ($i = 0; $i < $counter; $i++): 
                $color = ($i % 2 == 0) ? 'even' : 'odd'; ?>
                <tr>
                <?php 
                foreach ($header_copy as $header): ?>
                    <?php if (isset($audit_data_copy['Audit Notes'][$i]) && isset($audit_data_copy['Descr'][$i])) {
                    if ($header === 'Tag Number') {
                    echo "<td class='$color' style='color:green;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$header][$i] ?? '') . "</td>";
                    }  else {
                    echo "<td class='$color'>" . htmlspecialchars($audit_data_copy[$header][$i] ?? '') . "</td>";
                    }
                } elseif ($header === 'Tag Number' && isset($audit_data_copy['Descr'][$i])) {
                    echo "<td class='$color' style='color:red;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$header][$i] ?? '') . "</td>";
                } else if ($header === 'Tag Number' && (!isset($audit_data_copy['Descr'][$i]) || !isset($audit_data_copy['Description'][$i]))) {
                    echo "<td class='$color' style='color:orange;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$header][$i] ?? '') . "</td>";
                } else {
                    echo "<td class='$color'>" . htmlspecialchars($audit_data_copy[$header][$i] ?? '') . "</td>";
                }
                    ?>

                 <?php endforeach; ?>

                          
                            </tr>
                                            <?php endfor; ?>
                    </tbody>
                            </table>
                                </div>
                                        </section>
<?php } else { ?>
    <h3>Department <?php echo htmlspecialchars((string) $audit_details['dept_id']); ?> Assets</h3>
    <table>
        <thead>
            <tr>
                <?php foreach ($headers_copy as $header): ?>
                    <th class='odd'><?php echo htmlspecialchars((string) $header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php  $j = 1;
            for ($i = 0; $i < $counter; $i++): 
                $color = ($i % 2 == 0) ? 'even' : 'odd'; ?>
                <tr>
                <?php 
                foreach ($audit_headers as $header): ?>
                    <?php if (isset($audit_data_copy[$i]['Audit Notes']) && isset($audit_data_copy[$i]['asset_name'])) {
                    if ($header === 'asset_tag') {
                    echo "<td class='$color' style='color:green;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$i][$header] ?? '') . "</td>";
                    }  else {
                    echo "<td class='$color'>" . htmlspecialchars($audit_data_copy[$i][$header] ?? '') . "</td>";
                    }
                } elseif ($header === 'asset_tag' && isset($audit_data_copy[$i]['asset_name'])) {
                    echo "<td class='$color' style='color:red;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$i][$header] ?? '') . "</td>";
                } else if ($header === 'asset_tag' && (!isset($audit_data_copy[$i]['asset_name']) || !isset($audit_data_copy[$i]['Description']))) {
                    echo "<td class='$color' style='color:orange;font-weight:700;'>" . htmlspecialchars($audit_data_copy[$i][$header] ?? '') . "</td>";
                } else {
                    echo "<td class='$color'>" . htmlspecialchars($audit_data_copy[$i][$header] ?? '') . "</td>";
                }
                    ?>

                 <?php endforeach; ?>

                          
                            </tr>
                                            <?php endfor; ?>
                    </tbody>
                            </table>
                                </div>
                                        </section>
                                            <?php } ?>
                                            </body>
</html>
<?php 
            $html = ob_get_clean();
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetTitle('Audit Details - ' . htmlspecialchars((string) $audit_details['dept_id']));
            $mpdf->SetAuthor(htmlspecialchars((string) $audit_details['auditor']));
            $mpdf->Output('audit-details-'.htmlspecialchars((string) $audit_details['dept_id']).'.pdf', 'D');


