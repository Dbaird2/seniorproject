<?php
include_once("navbar.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    $mail = new PHPMailer(true);
    try {
        /*
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dason-16@hotmail.com';
        $mail->Password   = 'tzzunttglwzrnlwn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('dason-16@hotmail.com', 'no-reply');
        $mail->addAddress($email);
        #$mail->SMTPDebug = 2; // or 3 for even more detail
        #$mail->Debugoutput = 'html'; // nice HTML formatting

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset';
        $mail->Body    = 'Click to reset your password: <a href="http://localhost/reset.php">Reset</a>';

        $mail->send();
        */
    } catch (Exception $e) {
        #echo "Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
body {
            display: flex;
            flex-direction: column; 
            justify-content: space-between; 
            height: 100vh; 
            margin: 0; 
        }
        .container {
            width:30rem;
            display: flex;
            flex-direction: column; 
            margin: auto;
            border: 1px solid black;
            border-radius: 15px;    
            box-shadow: 2px 2px 2px #003594;

        }
        #form-label {
            margin-bottom:0.5em;
            margin-left:0.5rem;
            color: black;
            font-weight:bold;
            font-size: 1.1rem;
            
        }
        .header {
            background-color: #003594;
            color: #FFC72C;
            text-align: center;
            border-top-left-radius: 14px;    
            border-top-right-radius: 14px;    
        }
        .formAtt {
            margin-bottom:1rem;
            width: 90%;
            color: rgb(36, 35, 42);
            font-size: 1rem;
            line-height: 20px;
            min-height: 1.8rem;
            border-radius: 4px;
            padding: 8px 16px;
            border: 2px solid transparent;
            box-shadow: rgb(0 0 0 / 12%) 0px 1px 3px, rgb(0 0 0 / 24%) 0px 1px 2px;
            background: rgb(251, 251, 251);
            transition: all 0.1s ease 0s;
            :focus{
                border: 2px solid #003594;
            }
        }
        .footer {
            text-align: center;
              
        }
       .button-58 {
            width: 50%;
            align-items: center;
            background-color: #003594;
            border: 2px solid #06f;
            box-sizing: border-box;
            color: #FFC72C;
            cursor: pointer;
            display: inline-flex;
            fill: #000;
            font-size: 16px;
            font-weight: 600;
            height: 5vh;
            justify-content: center;
            letter-spacing: -.8px;
            line-height: 24px;
            min-width: 140px;
            outline: 0;
            padding: 0 17px;
            text-align: center;
            text-decoration: none;
            transition: all .3s;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            border-radius: 30px;
            -ms-transform: translate(50%, 50%);
            transform: translate(50%, 50%);
        }

        .button-58:focus {
        color: #171e29;
        }

        .button-58:hover {
        background-color: #3385ff;
        border-color: #3385ff;
        fill: #06f;
        }

        .button-58:active {
        background-color: #3385ff;
        border-color: #3385ff;
        fill: #06f;
        }

        @media (min-width: 768px) {
            .button-58 {
                min-width: 170px;
            }
        }
        .forgot-pass {
            text-align:right;
            display:block;
            margin-right:1.5rem;
            text-decoration: none;
            
        }
        a:hover {
            text-decoration: underline;
        }
        a:visited  {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reset Password</h2>
        </div>
            <div class="body">
                <form id="login-form" method="post" action="login.php" oninput="validateForm()" onsubmit="return validateForm()">
                    <label id="form-label" for="email">Email Address</label>
                    <input class="formAtt" type="text" name="email" id="email" placeholder="example@csub.edu"  required>
                    <div id="err_email" style="color:red;"></div>
                    <button id="btn" class="button-58" role="button">Reset</button>
                </form>
                <div class="footer">
                    <br>
                    <p></p> 
                </div>
        </div>
    </div>
    <!-- 4. Display success or error messages here -->
</body>
</html>