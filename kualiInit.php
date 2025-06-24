<?php

$subdomain = "subdomain";
$apikey = "apikey";

$url = "";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
	"Content-Type: application/json",
	"Authorization: Bearer {$apikey}",
);

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = '{"query": "mutation" ($appId: ID!) { InitializeWorkflow(args: {id:
$appId}) {actionId}}", "variables": {
	"appId": ""
}}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//debug
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);
var_dump($resp);


