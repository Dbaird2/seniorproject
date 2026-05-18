<?php
/*
TO USE IN config.php

require_once 'folder_name/kuali_class.php'
$kuali = new KualiAPI();

UPDATE IN auth/2fa.php RIGHT AFTER SETTING $_SESSION['email']
$kuali_key = $query_repo->getAPIKey($_SESSION['email'])['kuali_key'];
$kuali->setAPIKey($kuali_key)

WHAT TO DO
DELETE ALL KUALI CODE FOR CURLS, KEEP WHAT IS BEING PASTED AS AN ARGUMENT LIKE $variables
REPLACE WITH $kuali->writeToKuali($app_id, $variable)

LOGIC
Build Variables -> Get app_id -> call Class function -> Done

This class I am not fully sure if it works, but I personally do not want to mess with testing it since I am out of the loop.

*/

class KualiAPI
{
    public $url = "https://csub.kualibuild.com/app/api/v0/graphql";
    private string $api_key;

    public function setAPIKey(string $api_key)
    {
        $this->api_key = $api_key;
    }

    public function writeToKuali(string $app_id, array $variables)
    {
        try {
            $curl = curl_init($this->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$this->api_key}",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $data = json_encode([
                "query" => 'mutation ($appId: ID!) { initializeWorkflow(args: {id: $appId}) { actionId }}',
                "variables" => [
                    "appId" => $app_id
                ]
            ]);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            $resp = curl_exec($curl);
            if (!$resp) {
                return ['error' => curl_error($curl)];
            }
            $decoded_data = json_decode($resp, true);

            if (!is_array($decoded_data)) {
                return ['error' => 'Invalid JSON response', 'raw' => $resp];
            }
            if (isset($decoded_data['errors'])) {
                return ['error' => $decoded_data['errors']];
            }

            $action_id1 = $decoded_data['data']['initializeWorkflow']['actionId'];


            $get_draft_id = json_encode([
                'query' => 'query ($actionId: String!) { action(actionId: $actionId) { id appId document { id } } }',
                'variables' => [
                    'actionId' => $action_id1
                ]
            ]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $get_draft_id);

            $resp = curl_exec($curl);

            if (!$resp) {
                return ['error' => curl_error($curl)];
            }

            $decoded_data = json_decode($resp, true);

            if (!is_array($decoded_data)) {
                return ['error' => 'Invalid JSON response', 'raw' => $resp];
            }
            if (isset($decoded_data['errors'])) {
                return ['error' => $decoded_data['errors']];
            }

            $document_id = $decoded_data['data']['action']['document']['id'];
            $action_id = $decoded_data['data']['action']['id'];
            $variables['documentId'] = $document_id;
            $variables['actionId'] = $action_id;
            $variables['status'] = 'completed';
            $submit_form = json_encode([
                'query' => 'mutation ($documentId: ID!, $data: JSON, $actionId: ID!, $status: String)
            { submitDocument( id: $documentId data: $data actionId: $actionId status: $status )}',
                'variables' => $variables
            ]);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $submit_form);

            $resp = curl_exec($curl);

            if (!$resp) {
                return ['error' => curl_error($curl)];
            }
            curl_close($curl);
            $res = json_decode($resp, true);
            $return_stmt = ['status' => $res['data']['submitDocument'], 'document_id' => $document_id];
            return json_encode($return_stmt);
        } catch (Exception $e) {
            error_log("Error Writing to Kuali: " . $e->getMessage());
            return ['error' => 'Error with Writing to Kuali'];
        }
    }

    public function kualiRead(string $app_id, int $skip = 0, int $limit = 10, string $tag = "")
    {
        try {
            $curl = curl_init($this->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer {$this->api_key}",
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
                    "skip" => $skip,
                    "limit" => $limit,
                    "sort" => ["meta.createdAt"],
                    "query" => $tag,
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

            $resp = curl_exec($curl);

            if (!$resp) {
                return ['error' => curl_error($curl)];
            }
            curl_close($curl);

            $decode_true = json_decode($resp, true);

            if (!is_array($decode_true)) {
                return ['error' => 'Invalid JSON response', 'raw' => $resp];
            }
            if (isset($decode_true['errors'])) {
                return ['error' => $decode_true['errors']];
            }

            return $decode_true;
        } catch (Exception $e) {
            error_log("Error reading from kuali: " . $e->getMessage());
            return ['error' => 'Exception occurred'];
        }
    }

    public function baseReads(string $app_id, int $skip = 0, int $limit = 200)
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $data = json_encode([
            "query" => 'query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { app(id: $appId) { id name documentConnection( args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID ) { totalCount edges { node { id data meta } } pageInfo { hasNextPage hasPreviousPage skip limit } } }}',
            "variables" => [
                "appId" => $app_id,
                "skip" => $skip,
                "limit" => $limit,
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

        $resp = curl_exec($curl);
        unset($curl);

        return json_decode($resp, true);
    }

    public function queryKualiFormStatus(string $app, $query)
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}",
        ]);

        $data = json_encode([
            "query" => 'query ( $appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) { 
                app(id: $appId) { 
                id name documentConnection( 
                args: { 
                skip: $skip limit: $limit sort: $sort query: $query fields: $fields } 
                keyBy: ID ) { 
                totalCount 
                edges { 
                node { id meta } 
                } 
                pageInfo { hasNextPage hasPreviousPage skip limit } } }}',
            "variables" => [
                "appId"  => $app,
                "skip"   => 0,
                "limit"  => 100,
                "sort"   => ["meta.updatedAt"],
                "query"  => trim($query),
                "fields" => [
                    "type" => "OR",
                    "operators" => [
                        ["field" => "meta.workflowStatus", "type" => "IS", "value" => "Complete"],
                        ["field" => "meta.updatedAt",      "type" => "RANGE", "min" => "0"]
                    ]
                ],
            ]
        ]);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($curl);
        unset($curl);
        return json_decode($resp, true);
    }

    public function dataworksCheckForms(string $app_id, $query)
    {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer {$this->api_key}",
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
                "query" => $query,
            ]
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($resp, true);
    }
}
