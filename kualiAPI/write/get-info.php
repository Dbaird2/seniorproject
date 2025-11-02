<?php
function getNameInfo($person_name, $dept_id) {
    global $dbh, $apikey, $action_id;
    $query = "SELECT dept_id[1], f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) = :full_name";
    $get_name_stmt = $dbh->prepare($query);
    $get_name_stmt->execute([":full_name"=>$person_name]);
    $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    if ($person_info) {
        searchName($person_name, $apikey, $dept_id);
        $get_name_stmt = $dbh->prepare($query);
        $get_name_stmt->execute([":full_name" => $person_name]);
        $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    }
    if ((empty($person_info['form_id']) || empty($person_info['school_id']))) {
        searchName($person_name, $apikey, $dept_id);
        $get_name_stmt = $dbh->prepare($query);
        $get_name_stmt->execute([":full_name" => $person_name]);
        $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $now_array = new DateTime();                              
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');
    $signature = [
        'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $person_info['f_name'].' '.$person_info['l_name'] . ' ('.$person_info['email'].')',
        'signatureType' => 'type',
        'signedName' => $person_info['signature'] ?? $person_info['f_name'].' '.$person_info['l_name'],
        'userId'  => $person_info['form_id'],
        'email'     => $person_info['email'],
        'firstName'    => $person_info['f_name'],
        'id'   => $person_info['form_id'],
        'label'     => $person_info['f_name'].' '.$person_info['l_name'],
        'lastName'    => $person_info['l_name'],
        'schoolId' => $person_info['school_id'],
        'username'  => $person_info['username']
    ];
    return $signature;
}

function getEmailInfo($email, $dept_id) {
    global $dbh, $apikey, $action_id;
    $query = "SELECT f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE email = :email";
    $get_name_stmt = $dbh->prepare($query);
    $get_name_stmt->execute([":email"=>$email]);
    $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    if ($person_info) {
        searchEmail($email, $apikey, $dept_id);
        $get_name_stmt = $dbh->prepare($query);
        $get_name_stmt->execute([":email" => $email]);
        $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $email_array = explode('@', $email);
    if ($email_array[0] !== $person_info['username']) {           
        $update_user = 'UPDATE user_table SET username = :username WHERE email = :email';               
        $update_stmt = $dbh->prepare($update_user);                                         
        $update_stmt->execute([':username' => $email_array[0], ":email" => $email]);
    }
    if ((empty($person_info['form_id']) || empty($person_info['school_id']))) {
        searchEmail($person_info['email'], $apikey, $dept_id);
        $get_name_stmt = $dbh->prepare($query);
        $get_name_stmt->execute([":email" => $person_info['email']]);
        $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $now_array = new DateTime();                              
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');
    $signature = [
        'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $person_info['f_name'].' '.$person_info['l_name'] . ' ('.$person_info['email'].')',
        'signatureType' => 'type',
        'signedName' => $person_info['signature'] ?? $person_info['f_name'].' '.$person_info['l_name'],
        'userId'  => $person_info['form_id'],
        'email'     => $person_info['email'],
        'firstName'    => $person_info['f_name'],
        'id'   => $person_info['form_id'],
        'label'     => $person_info['f_name'].' '.$person_info['l_name'],
        'lastName'    => $person_info['l_name'],
        'schoolId' => $person_info['school_id'],
        'username'  => $person_info['username']
    ];
    return $signature;
}

function getSubmitterSig() {
    global $dbh, $action_id;
    $now_array = new DateTime();                              
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');

    $select = "SELECT email, kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
    $email = $_SESSION['email'];
    $select_stmt = $dbh->prepare($select);
    $select_stmt->execute([":email" => $_SESSION['email']]);
    $sub = $select_stmt->fetch(PDO::FETCH_ASSOC);
    $email_array = explode('@', $email);
    if (empty($sub['school_id']) || empty($sub['form_id'])) {
        searchEmail($email_array[0], $sub['kuali_key'], $_SESSION['deptid']);
        $select_stmt = $dbh->prepare($select);
        $select_stmt->execute([":email" => $_SESSION['email']]);
        $sub = $select_stmt->fetch(PDO::FETCH_ASSOC);
    }
    $signature = [
        'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $sub['f_name'].' '.$sub['l_name'] . ' ('.$sub['email'].')',
        'signatureType' => 'type',
        'signedName' => $sub['signature'] ?? $sub['f_name'].' '.$sub['l_name'],
        'userId'  => $sub['form_id'],
        'apikey' => $sub['kuali_key'],
        'fullName' => $sub['f_name'] . ' ' . $sub['l_name'],
        'schoolId' => $sub['schoolId']
    ];
    return $signature;
}
