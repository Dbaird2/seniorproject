<?php
//caroline contreras
//wow
$subdomain = "subdomain";
$apikey = "apikey";

$url = "";

//initialize a cURL session
$curl = curl_init($url);
//Sets URL to fetch
curl_setopt($curl, CURLOPT_URL, $url);

curl_setopt($curl, CURLOPT_POST, true);
//Return the transfer as a string
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
	"Content-Type: application/json",
	"Authorization: Bearer {$apikey}",
);

// ...(cURL handle returned by curl_init(), Option you want to set, value you want to set)
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);



$data = '{"query": "mutation" ($appId: ID!) { InitializeWorkflow(args: {id:
$appId}) {actionId}}", "variables": {
	"appId": ""
}}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//debug
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

//execute a cURL session
$resp = curl_exec($curl);

//Close the cURL session
curl_close($curl);
var_dump($resp);


