<?php
function checkForm($id, $tag, $app_id) {
    global $dept_id, $dept_name, $audit_id, $dbh, $apikey, $form_info;
    $select = "SELECT bulk_transfer_time, kuali_key FROM kuali_table";
    $select_stmt = $dbh->query($select);
    $result = $select_stmt->fetch(PDO::FETCH_ASSOC);

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
        "query" => 'query ( 
            $appId: ID! 
            $skip: Int! 
            $limit: Int! 
            $sort: [String!] 
            $query: String 
            $fields: Operator
    ) { 
        app(id: $appId) { 
        id name documentConnection( 
            args: { 
            skip: $skip 
                limit: $limit 
                sort: $sort 
                query: $query 
                fields: $fields 
        } 
        keyBy: ID 
        ) { 
            totalCount edges { 
            node { id data meta } } 
                pageInfo { hasNextPage hasPreviousPage skip limit } 
        } 
    }
}',
    "variables" => [
        "appId" => $app_id,
        "skip" => 0,
        "limit" => 100,
        "sort" => ["meta.createdAt"],
        "query" => $tag,
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
    $tag_regex = '/\b('.$tag.')\b/i';
    $form_status = '';
    foreach ($edges as $index => $edge) {
        $update_time = $edge['node']['meta']['createdAt'];
        $form_id = $edge['node']['id'];
        $regex = '/\b('.$tag.')\b/i';
        if ($id === $form_id) {
            $status = $edge['node']['meta']['workflowStatus'];
            if ($status === 'In Progress') {
                return false;
            }
            if ($status === 'Denied') {
                $form_status = 'denied';
            }
            if ($status === 'Complete') {
                $form_status = 'complete';
            }

            foreach ($array as $form_index => $form) {
                $form = trim($form, '{}"');
                if (preg_match($tag_regex, $form)) {
                    $new_form = str_replace('in-progress', $form_status, $form);
                    break;
                }
                echo "<br>" . $new_form . "<br>";
            }
            $update = 'UPDATE audit_history SET check_forms = ARRAY_APPEND(check_forms, :complete) WHERE audit_id = :aid AND dept_id = :dept_id';
            $stmt = $dbh->prepare($update);
            $stmt->execute([':aid'=>$audit_id, ':dept_id'=>$dept_id, ':complete'=>$new_form]);
            $update = 'UPDATE audit_history SET check_forms = ARRAY_REMOVE(check_forms, :in_progress) WHERE audit_id = :aid AND dept_id = :dept_id';
            $stmt = $dbh->prepare($update);
            $stmt->execute([':in_progress'=>$form, ':aid'=>$audit_id, ':dept_id'=>$dept_id, ]);
            return true;
        }
    }
    return false;
}

