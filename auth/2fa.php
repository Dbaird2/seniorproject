<?php
require_once __DIR__ . '/../vendor/autoload.php';
include_once ('../config.php');

use OTPHP\TOTP;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
try {
    $email = '';
    if (!empty($_GET['email'])) {
        $id = trim($_GET['id']);
        $email = trim($_GET['email']);
        $dept_id = trim($_GET['dept_id']);
        $role = trim($_GET['role']);
    } else {
        $id = trim($_POST['id']);
        $email = trim($_POST['email']);
        $dept_id = trim($_POST['dept_id']);
        $role = trim($_POST['role']);
    }
    $stmt = $dbh->prepare("SELECT totp_secret FROM user_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch();

    $showQr   = false;
    $qrDataUri = null;
    $secret    = null;

    if ($row && !empty($row['totp_secret'])) {
        // User already has a TOTP secret, load it
        $totp = TOTP::create(
            secret: $row['totp_secret'],
            period: 30,
            digits: 6
        );
        $totp->setLabel($email);
        $totp->setIssuer('Dataworks');
        if (empty($user['totp_confirmed_at'])) {
            $secret  = $row['totp_secret'];
            $otpauth = $totp->getProvisioningUri();
            $builder = new Builder(
                writer: new PngWriter(),
                data: $otpauth,
                encoding: new Encoding('UTF-8'),
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin
            );
            $result    = $builder->build();
            $qrDataUri = $result->getDataUri();
            $showQr    = true;
        }
    } else {
        // New user, store the generated secret
        $totp = TOTP::create(
            secret: null,     // auto-generate secure secret
            period: 30,       // 30s default
            digits: 6        // 6-digit codes
        );
        $totp->setLabel($email);
        $totp->setIssuer('Dataworks'); // shows as the "account provider" in apps
        $secret = $totp->getSecret();
        $stmt = $dbh->prepare("UPDATE user_table SET totp_secret = :secret WHERE email = :email");
        $stmt->bindParam(':secret', $secret);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $otpauth = $totp->getProvisioningUri();
        $builder = new Builder(
            writer: new PngWriter(),
            data: $otpauth,
            encoding: new Encoding('UTF-8'),
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result   = $builder->build();
        $qrDataUri = $result->getDataUri();
        $showQr    = true;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $code = trim($_POST['code'] ?? '');
        if ($code !== '') {
            if ($totp->verify($code)) {
                $_SESSION['id'] = $id;
                $_SESSION['role'] = $role;
                $_SESSION['email'] = $email;
                $_SESSION['deptid'] = trim($dept_id, '{"}');
                header('Location: ../home.php');
                exit;
            } else {
                echo '<p>Invalid code. Please try again.</p>';
            }
        }
    }
    $qrDataUri = $result->getDataUri(); // use in <img src="<?= htmlspecialchars($qrDataUri)
} catch (PDOException $e) {
    error_log($e->getMessage());
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enable 2FA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #003DA5 0%, #002560 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 61, 165, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        /* Added CSUB header with blue and gold colors */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #FFB81C;
            padding-bottom: 20px;
        }

        h1 {
            color: #003DA5;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #FFB81C;
            font-size: 14px;
            font-weight: 600;
        }

        /* Styled setup instructions with CSUB colors */
        .setup-section {
            background: #f8f9ff;
            border-left: 4px solid #FFB81C;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .setup-section h2 {
            color: #003DA5;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .setup-section ol {
            list-style-position: inside;
            color: #333;
            line-height: 1.8;
        }

        .setup-section li {
            margin-bottom: 8px;
        }

        /* Styled QR code display */
        .qr-container {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background: #fff9e6;
            border: 2px solid #FFB81C;
            border-radius: 8px;
        }

        .qr-container img {
            max-width: 250px;
            border: 3px solid #003DA5;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }

        /* Styled secret code display */
        .secret-code {
            margin: 20px 0;
            text-align: center;
        }

        .secret-code label {
            color: #003DA5;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .secret-code code {
            display: block;
            background: #003DA5;
            color: #FFB81C;
            padding: 12px 16px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            word-break: break-all;
        }

        /* Styled form elements with CSUB colors */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #003DA5;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #003DA5;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #FFB81C;
            box-shadow: 0 0 0 3px rgba(255, 184, 28, 0.1);
        }

        /* Styled submit button with CSUB colors */
        button {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #003DA5 0%, #002560 100%);
            color: #FFB81C;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(135deg, #FFB81C 0%, #ff9800 100%);
            color: #003DA5;
            box-shadow: 0 10px 25px rgba(0, 61, 165, 0.2);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        /* Error message styling */
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .text-center {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 15px;
        }

        .text-center a {
            color: #003DA5;
            text-decoration: none;
            font-weight: 600;
        }

        .text-center a:hover {
            color: #FFB81C;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Two-Factor Authentication</h1>
            <p class="subtitle">Secure your Dataworks account</p>
        </div>

        <?php if (empty($row['totp_secret'])) { ?>
        <div class="setup-section">
            <h2>Setup Instructions</h2>
            <ol>
                <li>Open Google Authenticator (or similar authenticator app)</li>
                <li>Tap "+" → "Scan a QR code"</li>
                <li>Scan the QR code below</li>
            </ol>
        </div>

        <div class="qr-container">
            <img src="<?= htmlspecialchars($qrDataUri, ENT_QUOTES) ?>" alt="TOTP QR Code" />
        </div>

        <div class="secret-code">
            <label>Or enter this secret manually:</label>
            <code><?= htmlspecialchars($secret, ENT_QUOTES) ?></code>
        </div>
    <?php } ?>

    <form method="post" action="2fa.php">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['error'])) { ?>
            <div class="error-message">
            Invalid code. Please try again.
            </div>
            <?php } ?>
           <div class="form-group">
           <label for="code">Enter the 6-digit code from your app:</label>
           <input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" 
            placeholder="000000" maxlength="6" required>
           </div>
            <input type="hidden" name="user_id" value="<?= (int)$userId ?>">
            <input type="hidden" name="id" value="<?= $_GET['id'] ?? $_POST['id'] ?>">
            <input type="hidden" name="role" value="<?= $_GET['role'] ?? $_POST['role'] ?>">
            <input type="hidden" name="email" value="<?= $email?>">
            <input type="hidden" name="dept_id" value="<?= $_GET['dept_id'] ?? $_POST['dept_id'] ?>">

            <button type="submit">Verify & Finish</button>
        </form>

        <p class="text-center">Having trouble? Contact <a href="mailto:distribution@csub.edu">support</a></p>
    </div>
</body>

</html>

