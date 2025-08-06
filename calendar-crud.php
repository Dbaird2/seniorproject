<?php
// Retrieve the raw POST body
$body = @file_get_contents('php://input');

// Decode the JSON data into a PHP associative array
$data = json_decode($body, true);

// Now, $data contains the webhook payload and can be processed
// For example, you can log it or perform actions based on the data
file_put_contents('webhook_log.txt', print_r($data, true), FILE_APPEND);

// Respond to the webhook provider to acknowledge receipt
http_response_code(200); // Send a 200 OK status
echo "Webhook received successfully!";
echo "<pre>";
var_dump($data);
echo "</pre>";
?>
