<?php
require __DIR__ . '/vendor/autoload.php';

use OTPHP\TOTP;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

$stmt = $dbh->prepare("SELECT totp_secret FROM users WHERE email = :email");
$stmt->bindParam(':email', $_GET['email']);
$stmt->execute();
$row = $stmt->fetch();

$showQr   = false;
$qrDataUri = null;
$secret    = null;

if ($row && !empty($row['totp_secret'])) {
    // User already has a TOTP secret, load it
    $totp = TOTP::create(
        secret: $user['totp_secret'],
        period: 30,
        digits: 6
    );
    $totp->setLabel($_GET['email']);
    $totp->setIssuer('Dataworks');
    if (empty($user['totp_confirmed_at'])) {
        $secret  = $user['totp_secret'];
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
    $totp->setLabel($_POST['email']);
    $totp->setIssuer('Dataworks'); // shows as the “account provider” in apps
    $secret = $totp->getSecret();
    $stmt = $dbh->prepare("UPDATE users SET totp_secret = :secret WHERE email = :email");
    $stmt->bindParam(':secret', $secret);
    $stmt->bindParam(':email', $_POST['email']);
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
            $_SESSION['id'] = trim($_POST['id']);
            $_SESSION['role'] = trim($_POST['role']);
            $_SESSION['email'] = trim($_POST['email']);
            $_SESSION['deptid'] = trim($_POST['dept_id'], '{}');
            header('Location: ../home.php');
            exit;
        } else {
            echo '<p>Invalid code. Please try again.</p>';
        }
    }
}
$qrDataUri = $result->getDataUri(); // use in <img src="<?= htmlspecialchars($qrDataUri) 
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Enable 2FA</title>
</head>

<body>
    <h1>Enable Two-Factor Authentication</h1>
    <ol>
        <li>Open Google Authenticator (or similar).</li>
        <li>Tap “+” → “Scan a QR code”.</li>
        <li>Scan this QR:</li>
    </ol>
    <img src="<?= htmlspecialchars($qrDataUri, ENT_QUOTES) ?>" alt="TOTP QR" />
    <p>Or enter this secret manually: <code><?= htmlspecialchars($secret, ENT_QUOTES) ?></code></p>

    <form method="post" action="2fa.php">
        <label>Enter the 6-digit code from your app:</label>
        <input name="code" inputmode="numeric" autocomplete="one-time-code" required>
        <input type="hidden" name="user_id" value="<?= (int)$userId ?>">
        <input type="hidden" name="id" value="<?= $_GET['id'] ?? $_POST['id'] ?>">
        <input type="hidden" name="role" value="<?= $_GET['role'] ?? $_POST['role'] ?>">
        <input type="hidden" name="email" value="<?= $_GET['email'] ?? $_POST['email'] ?>">
        <input type="hidden" name="dept_id" value="<?= $_GET['dept_id'] ?? $_POST['dept_id'] ?>">
        <button type="submit">Verify & Finish</button>
    </form>
</body>

</html>
