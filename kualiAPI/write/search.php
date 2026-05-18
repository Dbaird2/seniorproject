<?php
function randomPassword()
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $pass[] = '-';
    $pass[] = '1';
    $pass[] = '2';
    $pass[] = '3';
    $pass[] = 'A';
    return implode($pass);
}
function searchName($search_name = '', $dept_id = '')
{
    $search_name = trim($search_name);
    $dept_id = trim($dept_id);
    include_once __DIR__ . "/../../vendor/autoload.php";
    global $query_repo, $kuali;
    $select = "SELECT dept_id[1], f_name, l_name, signature, email, form_id, school_id, username FROM user_table WHERE CONCAT(f_name, ' ' ,l_name) ILIKE ?";
    $info = $query_repo->fetchOne($select, '%' . $search_name . '%');
    if ($info) {
        if (!empty($info['form_id']) && !empty($info['school_id'])) {
            return;
        }
    }
    $name_array = explode(' ', $search_name);
    $user_f_name = $name_array[0];
    $user_l_name = $name_array[1];
    if (!empty($name_array[2])) {
        $user_l_name .= ' ' . $name_array[2];
    }
    if (!empty($name_array[3])) {
        $user_l_name .= ' ' . $name_array[3];
    }
    if (!empty($name_array[4])) {
        $user_l_name .= ' ' . $name_array[4];
    }

    if (!empty($info['username'])) {
        $search_name = $info['username'];
    }

    $resp = $kuali->searchKuali($search_name);

    $name_data = json_decode($resp, true);

    $name_edges = $name_data['data']['usersConnection']['edges'];

    foreach ($name_edges as $info) {
        $id = $info['node']['id'];
        $display_name = $info['node']['displayName'];
        $email = $info['node']['email'];
        $username = $info['node']['username'];
        $f_name = $info['node']['firstName'];
        $l_name = $info['node']['lastName'];
        $schoolid = $info['node']['schoolId'];
        if (strtolower(trim($f_name)) !== strtolower(trim($user_f_name)) || strtolower(trim($l_name)) !== strtolower(trim($user_l_name))) {
            continue;
        }
        $user = $query_repo->getUserInfo($email);
        if (!$user) {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);

            $insert = "INSERT INTO user_table (form_id, username, email, f_name, l_name, school_id, u_role, pw, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $query_repo->execute($insert, trim($id), trim($username), trim($email), trim($f_name), trim($l_name), trim($schoolid), 'user', $hashed_pw, '{' . trim($dept_id) . '}');
            try {
                /*
                                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                                    $mail->isSMTP();
                                    $mail->Host       = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = 'dasonbaird25@gmail.com';
                                    $mail->Password   = $_SESSION['app_pass'];
                                    $mail->SMTPSecure = 'tls';
                                    $mail->Port       = 587;
                                    $mail->isHTML(true);
                                    $mail->CharSet = 'UTF-8';
                                    $mail->setFrom('dasonbaird25@gmail.com', 'Dataworks No Reply');
                                    $mail->addAddress($email, 'User');
                                    $mail->Subject = 'Account Auto Create';
                                    $mail->Body    = '<p><strong>This email is to notify you of your automatic account creation for dataworks<strong>. <br>Dataworks is Senior Project group project designed to help with auditing and asset tracking.
                                        Email: ' . $email . '<br>Password: ' . $pw . '<br>If you have any questions, concerns, or issues, feel free to reach out to distribution@csub.edu for more info.</p><br>
                                        <a href="https://dataworks-7b7x.onrender.com">Dataworks Link</a>';
                                    $mail->AltBody = 'Click this link to access Dataworks...';

                                    $mail->send();
                */
            } catch (Exception $e) {
                error_log("Error sending email: " . $e->getMessage());
                return;
            }
        } else {
            $update = "UPDATE user_table SET ";
            $count = 0;
            $params = [];
            if (empty($user['school_id'])) {
                $count++;
                $update .= 'school_id = ?';
                $params[] = $schoolid;
            }
            if (empty($user['form_id'])) {
                if ($count == 1) {
                    $update .= ', form_id = ?';
                } else {
                    $update .= ', form_id = ?';
                }
                $count++;
                $params[] = $id;
            }
            $update .= " WHERE email = ?";
            $params[] = $email;
            if ($count > 0) {
                $query_repo->execute($update, ...$params);
            }
        }
    }
}
function searchEmail($email = '', $dept_id = '')
{
    $email = trim($email);
    $dept_id = trim($dept_id);
    include_once __DIR__ . "/../../vendor/autoload.php";
    global $query_repo, $kuali;
    $email_array = explode('@', $email);
    $input_username = $email_array[0];

    $resp = $kuali->searchKuali($input_username);

    $name_data = json_decode($resp, true);
    $name_edges = $name_data['data']['usersConnection']['edges'];
    foreach ($name_edges as $info) {
        $id = $info['node']['id'];
        $display_name = $info['node']['displayName'];
        $email = $info['node']['email'];
        $username = $info['node']['username'];
        $f_name = $info['node']['firstName'];
        $l_name = $info['node']['lastName'];
        $schoolid = $info['node']['schoolId'];
        if (strtolower(trim($f_name)) !== strtolower(trim($user_f_name)) || strtolower(trim($l_name)) !== strtolower(trim($user_l_name))) {
            continue;
        }
        $user = $query_repo->getUserInfo($email);
        if (!$user) {
            $pw = randomPassword();
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);

            $insert = "INSERT INTO user_table (form_id, username, email, f_name, l_name, school_id, u_role, pw, dept_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $query_repo->execute($insert, trim($id), trim($username), trim($email), trim($f_name), trim($l_name), trim($schoolid), 'user', $hashed_pw, '{' . trim($dept_id) . '}');
            try {
                /*
                                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                                    $mail->isSMTP();
                                    $mail->Host       = 'smtp.gmail.com';
                                    $mail->SMTPAuth   = true;
                                    $mail->Username   = 'dasonbaird25@gmail.com';
                                    $mail->Password   = $_SESSION['app_pass'];
                                    $mail->SMTPSecure = 'tls';
                                    $mail->Port       = 587;
                                    $mail->isHTML(true);
                                    $mail->CharSet = 'UTF-8';
                                    $mail->setFrom('dasonbaird25@gmail.com', 'Dataworks No Reply');
                                    $mail->addAddress($email, 'User');
                                    $mail->Subject = 'Account Auto Create';
                                    $mail->Body    = '<p><strong>This email is to notify you of your automatic account creation for dataworks<strong>. <br>Dataworks is Senior Project group project designed to help with auditing and asset tracking.
                                        Email: ' . $email . '<br>Password: ' . $pw . '<br>If you have any questions, concerns, or issues, feel free to reach out to distribution@csub.edu for more info.</p><br>
                                        <a href="https://dataworks-7b7x.onrender.com">Dataworks Link</a>';
                                    $mail->AltBody = 'Click this link to access Dataworks...';

                                    $mail->send();
                */
            } catch (Exception $e) {
                error_log("Error sending email: " . $e->getMessage());
                return;
            }
        } else {
            $update = "UPDATE user_table SET ";
            $count = 0;
            $params = [];
            if (empty($user['school_id'])) {
                $count++;
                $update .= 'school_id = ?';
                $params[] = $schoolid;
            }
            if (empty($user['form_id'])) {
                if ($count == 1) {
                    $update .= ', form_id = ?';
                } else {
                    $update .= ', form_id = ?';
                }
                $count++;
                $params[] = $id;
            }
            $update .= " WHERE email = ?";
            $params[] = $email;
            if ($count > 0) {
                $query_repo->execute($update, ...$params);
            }
        }
    }
}
