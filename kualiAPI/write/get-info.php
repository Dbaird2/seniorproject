<?php
function getNameInfo($person_name, $dept_id)
{
    $person_name = trim($person_name);
    $dept_id = trim($dept_id);
    global $query_repo, $kuali;
    $query = "SELECT dept_id[1], f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) ILIKE ?";
    $person_info = $query_repo->fetchOne($query, '%' . $person_name . '%');
    if (!is_array($person_info)) {
        searchName($person_name, $dept_id);
        $person_info = $query_repo->fetchOne($query, '%' . $person_name . '%');
    }
    if ((empty($person_info['form_id']) || empty($person_info['school_id']))) {
        searchName($person_name, $dept_id);
        $person_info = $query_repo->fetchOne($query, '%' . $person_name . '%');
    }
    $now_array = new DateTime();
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');
    $signature = [
        // 'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $person_info['f_name'] . ' ' . $person_info['l_name'] . ' (' . $person_info['email'] . ')',
        'signatureType' => 'type',
        'signedName' => $person_info['signature'] ?? $person_info['f_name'] . ' ' . $person_info['l_name'],
        'userId'  => $person_info['form_id'],
        'email'     => $person_info['email'],
        'firstName'    => $person_info['f_name'],
        'id'   => $person_info['form_id'],
        'label'     => $person_info['f_name'] . ' ' . $person_info['l_name'],
        'lastName'    => $person_info['l_name'],
        'schoolId' => $person_info['school_id'],
        'username'  => $person_info['username']
    ];
    return $signature;
}

function getEmailInfo($email, $dept_id)
{
    global $query_repo;
    $person_info = $query_repo->getUserInfo($email);
    if ($person_info) {
        searchEmail($email, $dept_id);
        $person_info = $query_repo->getUserInfo($email);
    }
    $email_array = explode('@', $email);
    if ($email_array[0] !== $person_info['username']) {
        $update_user = 'UPDATE user_table SET username = ? WHERE email = ?';
        $query_repo->execute($update_user, $email_array[0], $email);
    }
    if ((empty($person_info['form_id']) || empty($person_info['school_id']))) {
        searchEmail($person_info['email'], $dept_id);
        $person_info = $query_repo->getUserInfo($email);
    }
    $now_array = new DateTime();
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');
    $signature = [
        // 'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $person_info['f_name'] . ' ' . $person_info['l_name'] . ' (' . $person_info['email'] . ')',
        'signatureType' => 'type',
        'signedName' => $person_info['signature'] ?? $person_info['f_name'] . ' ' . $person_info['l_name'],
        'userId'  => $person_info['form_id'],
        'email'     => $person_info['email'],
        'firstName'    => $person_info['f_name'],
        'id'   => $person_info['form_id'],
        'label'     => $person_info['f_name'] . ' ' . $person_info['l_name'],
        'lastName'    => $person_info['l_name'],
        'schoolId' => $person_info['school_id'],
        'username'  => $person_info['username']
    ];
    return $signature;
}

function getSubmitterSig()
{
    global $query_repo;
    $now_array = new DateTime();
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');

    $sub = $query_repo->getUserInfo($_SESSION['email']);
    $email_array = explode('@', $_SESSION['email']);
    if (empty($sub['school_id']) || empty($sub['form_id'])) {
        searchEmail($email_array[0], $sub['kuali_key'], $_SESSION['deptid']);
        $sub = $query_repo->getUserInfo($_SESSION['email']);
    }
    $signature = [
        // 'actionId'    => $action_id,
        'date'     => $now,
        'displayName'   => $sub['f_name'] . ' ' . $sub['l_name'] . ' (' . $sub['email'] . ')',
        'signatureType' => 'type',
        'signedName' => $sub['signature'] ?? $sub['f_name'] . ' ' . $sub['l_name'],
        'userId'  => $sub['form_id'],
        'apikey' => $sub['kuali_key'],
        'fullName' => $sub['f_name'] . ' ' . $sub['l_name'],
        'schoolId' => $sub['schoolId']
    ];
    return $signature;
}
