<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
include_once "../config.php";
$decoded_data = file_get_contents('php://input');
$data = json_decode($decoded_data, true);
$pw = trim($data['pw']);
$email = trim($data['email']);
if (!empty($email) || !empty($pw)) {
    echo json_encode(['status'=>'Failed to login']);
    exit;
}


try {
    $select = "SELECT phone_key, pw FROM user_table WHERE (email = :email OR username = :email) limit 1";
    $stmt = $dbh->prepare($select);
    $stmt->execute([":email"=>$email]);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
    exit;
}
if ($stmt->rowCount() > 0) {
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    if (password_verify($pw, $info['pw'])) {
        $length = 32; // Number of bytes for the random string, results in 64 hex characters
        $api_key = bin2hex(random_bytes($length));

        try {
            $update = "UPDATE user_table SET phone_key = :api WHERE email = :email OR username = :email";
            $stmt = $dbh->prepare($update);
            $stmt->execute([':api'=>$api_key, ":email"=>$email]);
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
            exit;
        }
        echo json_encode(['status'=>'Successfully logged in','api_key'=>$api_key]);
        exit;
    }

} else {
    try {
        $select_user = "SELECT username FROM user_table WHERE (email = :email OR username = :email) limit 1";
        $stmt = $dbh->prepare($select);
        $stmt->execute([":email"=>$email]);
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
        exit;
    }
    if ($stmt->rowCount() > 0) {
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($pw, $info['pw'])) {
            $length = 32; // Number of bytes for the random string, results in 64 hex characters
            $api_key = bin2hex(random_bytes($length));
            try {
                $update = "UPDATE user_table SET phone_key = :api WHERE email = :email OR username = :email";
                $stmt = $dbh->prepare($update);
                $stmt->execute([':api'=>$api_key, ":email"=>$email]);
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                echo json_encode(['status'=>'Error with database', 'error'=>$msg]);
                exit;
            }
            echo json_encode(['status'=>'Successfully logged in','api_key'=>$api_key]);
            exit;
        }
    }
}
echo json_encode(['status'=>'Failed to login']);
exit;
