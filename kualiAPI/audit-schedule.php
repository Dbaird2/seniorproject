<?php
include_once __DIR__ . "/../config.php";
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$select = "SELECT * FROM kuali_table";
$select_stmt = $dbh->query($select);
$result = $select_stmt->fetch(PDO::FETCH_ASSOC);
$raw_ms = (int)$result['schedule_time'] ?? 0;
$highest_time = date('c', $raw_ms / 1000);

$apikey = $result['kuali_key'];

$url = "https://csub.kualibuild.com/app/api/v0/graphql";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "Content-Type: application/json",
    "Authorization: Bearer {$apikey}",
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$data = json_encode([
    "query" => 'query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { app(id: $appId) { id name documentConnection( args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID ) { totalCount edges { node { id data meta } } pageInfo { hasNextPage hasPreviousPage skip limit } } }}',
    "variables" => [
        "appId" => "682622ce355ca4027e35d52a",
        "skip" => $raw_ms,
        "limit" => 200,
        "sort" => [
            "meta.createdAt"
        ],
        "query" => "",
        "fields" => [
            "type" => "AND",
            "operators" => [
                [
                    "field" => "meta.workflowStatus",
                    "type" => "IS",
                    "value" => "Complete"
                ]
            ]
        ]
    ]
]);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
$resp2 = json_decode($resp);

$decode_true = json_decode($resp, true);
$edges = $decode_true['data']['app']['documentConnection']['edges'];

try {
    foreach ($edges as $index => $edge) {
        $raw_ms++;

        $time = $edge['node']['data']['tYz59qALVK'];
        $date = $edge['node']['data']['ChU6eQjeRf'];
        $new_date = $time+($date)/1000;
        $date = new DateTime("@$new_date");
        $date = $date->format('m/d/Y H:i:s');

        $now = microtime(true);
        $now = (int)$now;
        if ($now > $new_date) {
            continue;
        }
        echo $date . '<br>';
        
        $custodian = $edge['node']['data']['Unwly2UM1p']['displayName'];
        echo $custodian. '<br>';

        if (!empty($edge['node']['data']['epSRSrkGXT'])) {
            $manager = $edge['node']['data']['epSRSrkGXT']['displayName'];
        }
        if (!empty($edge['node']['data']['G_0VlXBs4s'])) {
            $departments = $edge['node']['data']['G_0VlXBs4s']['data'];
            foreach ($departments as $dept) {
                $dept_id = $dept['data']['dTFWWegtgK']['data']['IOw4-l7NsM'];
                $dept_name = $dept['data']['dTFWWegtgK']['data']['AkMeIWWhoj'];
                echo $dept_id . ' ' . $dept_name . '<br>';
                $insert = 'INSERT INTO audit_schedule (dept_id, audit_time, custodian) VALUES (?, ?, ?)';
                $stmt = $dbh->prepare($insert);
                $stmt->execute([$dept_id, $date, $custodian]);
                if (!isset($manager) && !empty($manager)) {
                    addDepartment($custodian, $manager, $dept_id, $dept_name);
                }
            }
        }
    }
    $update_kuali = "UPDATE kuali_table SET schedule_time = :time";
    $update_stmt = $dbh->prepare($update_kuali);
    $update_stmt->execute([":time" => $raw_ms]);
} catch (PDOException $e) {
    echo "Error with database " . $e->getMessage();
    exit;
}
echo '<pre>' . json_encode(json_decode($resp), JSON_PRETTY_PRINT) . '</pre>';
exit;
function addDepartment($c_display_name, $m_full_name, $dept_id, $dept_name)
{
  global $dbh;
  echo '<br>Add Department<br>';
  echo ' Cust full name: ' . $c_display_name . ' Manager Full Name ' . $m_full_name . ' Dept Id ' . $dept_id . ' Dept Name ' . $dept_name;
  $select_dept = "SELECT dept_id, dept_manager FROM department WHERE dept_id = :dept_id";
  $dept_stmt = $dbh->prepare($select_dept);
  $dept_stmt->execute([":dept_id" => $dept_id]);
  $dept_info = $dept_stmt->fetch(PDO::FETCH_ASSOC);
  if ($dept_info) {
    if ($dept_info['dept_manager'] !== $m_full_name) {
      $update_dept = "UPDATE department SET dept_manager = :manager WHERE dept_id = :dept_id";
      $stmt = $dbh->prepare($update_dept);
      $stmt->execute([':manager' => $m_full_name, ':dept_id' => $dept_id]);
    }
    $select_cust = 'SELECT dept_id, form_id, document_set_id FROM department WHERE :cust = ANY(custodian)';
    $stmt = $dbh->prepare($select_cust);
    $stmt->execute([':cust' => $c_display_name]);
    $info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $found = false;
    foreach ($info as $row) {
      if ($row['dept_id'] === $dept_id) {
        $found = true;
      }
    }
    if (!$found) {
      $update = 'UPDATE department SET custodian = ARRAY_APPEND(custodian, :cust) WHERE dept_id = :id';
      $update_stmt = $dbh->prepare($update);
      $update_stmt->execute([':cust' => $c_display_name, ':id' => $dept_id]);
    }
  } else {
    $insert = 'INSERT INTO department (dept_id, dept_name, custodian, dept_manager) VALUES (?, ?, ?, ?)';
    $insert_stmt = $dbh->prepare($insert);
    $custodian = '{' . $c_display_name . '}';
    $insert_stmt->execute([$dept_id, $dept_name, $custodian, $m_full_name]);
  }
}
