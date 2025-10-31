<?php
include_once __DIR__ . '/../config.php';
checkFormStatus();
function checkFormStatus() {
    global $dbh;
    $get_key_select = 'SELECT kuali_key FROM user_table WHERE email = :email';
    $stmt = $dbh->prepare($get_key_select);
    $stmt->execute([':email'=>$_SESSION['email']]);
    $apikey = $stmt->fetchColumn();
    $subdomain = "csub";
    $url = "https://{$subdomain}.kualibuild.com/app/api/v0/graphql";

    $select = "select unnest(check_forms) AS form_id, dept_id, audit_id from audit_history where check_forms is not null and CAST(check_forms AS TEXT) ILIKE '%in-progress%'";
    $stmt = $dbh->query($select);
    $forms_to_check = $stmt->fetchAll();
    foreach ($forms_to_check as $form) { 
        $seperate = explode(',', $form['form_id']);
        $id = '';
        foreach ($seperate as $index => $ele) {
            if ($index === 0) {
                $id = trim($ele);
                continue;
            }
            if ($index === 1) {
                $type = match (trim($ele)) {
                'lsd' => '68e94e8a58fd2e028d5ec88f',
                    'rlsd' => '68d09e41d599f1028a9b9457',
                    'transfer' => '68c73600df46a3027d2bd386',
                    'rtransfer' => '68d09e38d599f1028a08969a',
            };
                continue;
            }
            if ($index === 2) {
                continue;
            }
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
                    "appId" => $type,
                    "skip" => 0,
                    "limit" => 100,
                    "sort" => [
                        "meta.updatedAt"
                    ],
                    "query" => trim($ele),
                    "fields" => [
                        "type" => "OR",
                        "operators" => [
                            [
                                "field" => "meta.workflowStatus",
                                "type" => "IS",
                                "value" => "Complete"
                            ],
                            [
                                "field" => "meta.updatedAt",
                                "type" => "RANGE",
                                "min" => "0"
                            ]
                        ]
                    ],
                ]
            ]);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);
            $decoded = json_decode($resp, true);
            $found = false;
            foreach ($decoded['data']['app']['documentConnection']['edges'] as $edge) {
                if (!empty($edge['node']['meta']['workflowStatus'])) {
                    $status = $edge['node']['meta']['workflowStatus'];
                    if ($id === trim($edge['node']['id'])) {
                        echo 'ID found <br>';
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    break;
                }
            }
            if ($found) {
                break;
            }
        }
        if ($found) {
            $status = strtolower(str_replace('<br>', '', $status));
            if ($status !== 'in progress') {
                $new_form = str_replace('in-progress', $status, $form['form_id']);
                $update = "UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, :new_form) WHERE audit_id = :id AND dept_id = :dept";
                $stmt = $dbh->prepare($update);
                $stmt->execute([':new_form' => $new_form, ':id' => $form['audit_id'], ':dept' => $form['dept_id']]);
                $update = "UPDATE audit_history SET check_forms = ARRAY_REMOVE(check_forms, :old_form) WHERE audit_id = :id AND dept_id = :dept";
                $stmt = $dbh->prepare($update);
                $stmt->execute([':old_form' => $form['form_id'], ':id' => $form['audit_id'], ':dept' => $form['dept_id']]);
            } else {
                $new_form = str_replace('in-progress', 'in-progress', $form['form_id']);
            }
        }
    }
}
