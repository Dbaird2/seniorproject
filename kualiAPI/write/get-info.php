<?php
function getSignature($person_name = '', $email = '', $type = 'signature', $action_id = '') {
    if (empty($query)) return;
    
    if (empty($person_name) && empty($email)) {
        return;
    }
    if ($type === 'signature' && empty($action_id)) {
        return;
    }
    if ($type !== 'signature' && $type !== 'info') {
        return;
    }
    global $dbh, $apikey, $dept_id;
    try {
        if (!empty($person_name)) {
            $query = "SELECT f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) = :full_name";
            $get_name_stmt = $dbh->prepare($query);
            $get_name_stmt->execute([":full_name"=>$person_name]);
            if ($get_name_stmt->rowCount() === 0) {
                searchName($person_name, $apikey, $dept_id);
                $get_name_stmt = $dbh->prepare($query);
                $get_name_stmt->execute([":full_name" => $person_name]);
            }
            $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!empty($email)) {
            $query = "SELECT f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE email = :email";
            $get_name_stmt = $dbh->prepare($query);
            $get_name_stmt->execute([":email"=>$email]);
            if ($get_name_stmt->rowCount() === 0) {
                searchEmail($email, $apikey, $dept_id);
                $get_name_stmt = $dbh->prepare($query);
                $get_name_stmt->execute([":email" => $email]);
            }
            $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
            $email_array = explode('@', $email);
            if ($email_array[0] !== $person_info['username']) {           
                $update_user = 'UPDATE user_table SET username = :username WHERE email = :email';               
                $update_stmt = $dbh->prepare($update_user);                                         
                $update_stmt->execute([':username' => $email_array[0], ":email" => $email]);
            }
        }
        if ((empty($person_info['form_id']) || empty($person_info['school_id']))) {
            if (!empty($email)) {
                searchEmail($person_info['email'], $apikey, $dept_id);
                $get_name_stmt = $dbh->prepare($query);
                $get_name_stmt->execute([":email" => $person_name]);
                $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                searchName($person_name, $apikey, $dept_id);
                $get_name_stmt = $dbh->prepare($query);
                $get_name_stmt->execute([":full_name" => $person_name]);
                $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        if (!empty($email)) {
            searchEmail($person_info['email'], $apikey, $dept_id);
            $get_name_stmt = $dbh->prepare($query);
            $get_name_stmt->execute([":email" => $person_name]);
            $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            searchName($person_name, $apikey, $dept_id);
            $get_name_stmt = $dbh->prepare($query);
            $get_name_stmt->execute([":full_name" => $person_name]);
            $person_info = $get_name_stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    $now_array = new DateTime();                              
    $now_array->setTimezone(new DateTimeZone('America/Los_Angeles'));
    $now = $now_array->format('Y-m-d\TH:i:s.v\Z');
    if ($type === 'signature') {
        $signature = [
            'actionId'    => $action_id,
            'date'     => $now,
            'displayName'   => $person_info['f_name'].' '.$person_info['l_name'] . ' ('.$person_info['email'].')',
            'signatureType' => 'type',
            'signedName' => $person_info['signature'] ?? $person_info['f_name'].' '.$person_info['l_name'],
            'userId'  => $person_info['form_id']
        ];
        return $signature;
    } else {
        $info = [
            'displayName' => $person_name,
            'email'     => $person_info['email'],
            'firstName'    => $person_info['f_name'],
            'id'   => $person_info['form_id'],
            'label'     => $person_info['f_name'].' '.$person_info['l_name'],
            'lastName'    => $person_info['l_name'],
            'schoolId' => $person_info['school_id'],
            'username'  => $person_info['username'],
        ];
        return $info;
    }
}
