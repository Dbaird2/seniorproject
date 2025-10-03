<?php
include_once '../../config.php';
$encoded_data = file_get_contents('php://input');
$data = json_decode($encoded_data, true);
$audit_id = $data['audit_id'];
$dept_id = $data['dept_id'];


