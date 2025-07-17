<?php
error_reporting(0);
require_once 'config.php';
check_auth();
include_once 'navbar.php';
$query = "SELECT * FROM asset_info NATURAL JOIN room_table
    ORDER BY date_added DESC LIMIT 10";

$user_query = "SELECT f_name, l_name, TO_CHAR(last_login, 'Month DD, yyyy HH12:MI AM') as recent_login FROM user_table ORDER BY last_login DESC LIMIT 5";

$asset_count = "SELECT COUNT(*) as total_assets FROM asset_info";

$weekly_adds = "SELECT COUNT(*) as weekly_adds FROM asset_info WHERE date_added >= NOW() - INTERVAL '1 week'";

$weekly_changes = "SELECT COUNT(*) as weekly_changes FROM complete_asset_view WHERE change_date >= NOW() - INTERVAL '1 week'";
try {
    $stmt = $dbh->prepare($query);
    $stmt_user = $dbh->prepare($user_query);
    $stmt_asset_count = $dbh->prepare($asset_count);
    $stmt_weekly_adds = $dbh->prepare($weekly_adds);
    $stmt_weekly_changes = $dbh->prepare($weekly_changes);

    if ($stmt->execute()) {
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $assets = [];
    }
    if ($stmt_user->execute()) {
        $users = $stmt_user->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $users = [];
    }
    if ($stmt_asset_count->execute()) {
        $asset_count = $stmt_asset_count->fetch(PDO::FETCH_ASSOC);
    } else {
        $asset_count = ['total_assets' => 0];
    }
    if ($stmt_weekly_adds->execute()) {
        $weekly_adds = $stmt_weekly_adds->fetch(PDO::FETCH_ASSOC);
    } else {
        $weekly_adds = ['weekly_adds' => 0];
    }
    if ($stmt_weekly_changes->execute()) {
        $weekly_changes = $stmt_weekly_changes->fetch(PDO::FETCH_ASSOC);
    } else {
        $weekly_changes = ['weekly_changes' => 0];
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management System</title>
</head>
<link rel="stylesheet" href="/index.css">
<body>
<div class="is-index">
    <section class="container">
        <div class="container-head">
            <div class="head-text">
                <h2>Asset Management Dashboard</h2>
                <p>Monitor and manage your organization's assets in real-time</p>
            </div>
            <div class="head-bot">

                <div class="small-box">
                    <span class="small-box-text"><?= $asset_count['total_assets'] ?><br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Total Assets</strong></span>
                </div>
                <div class="small-box">
                    <span class="small-box-text"><?= $weekly_changes['weekly_changes'] ?><br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Weekly Change(s)</strong></span>
                </div>
                <div class="small-box">
                    <span class="small-box-text"><?= $weekly_adds['weekly_adds'] ?><br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Weekly Add(s)</strong></span>
                </div>
            </div>
        </div>
        <br>
    <div class="recent-asset-change">
            <div class="recent-assets">
                <h3>Recent Asset Additions</h3>
                <div class="asset-item asset-even asset-header">
                    <div class="asset-id">Asset Tag</div>
                    <div class="asset-status">Asset Name</div>
                    <div class="asset-category">Asset Type</div>
                    <div class="asset-location">In Room Tag</div>
                    <div class="asset-price">Price</div>
                    <div class="asset-price">Department</div>

                </div>
<?php
foreach ($assets as $key => $asset) {
?>
                <div class="asset-item asset-odd">
                    <div class="asset-id"><?= $asset['asset_tag'] ?></div>
                    <div class="asset-status"><?= $asset['asset_name'] ?></div>
                    <div class="asset-category"><?= $asset['asset_type'] ?></div>
                    <div class="asset-location"><?= $asset['room_tag'] ?></div>
                    <div class="asset-price">$<?= $asset['asset_price'] ?></div>
                    <div class="asset-deptid"><?= $asset['dept_id'] ?></div>
                </div>
<?php
}
?>

            </div>
            <div class="activity">
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="option"><a href="#add_asset.php">➕Add Asset</a></div>
                    <div class="option"><a href="auditing.php">Start Audit</a></div>
                    <div class="option"><a href="#change_asset_tag.php">Search Assets</a></div>
                    <div class="option"><a href="help.php">Help</a></div>
                </div>
                <div class="recent-activity">
                    <h3>Recent Activity</h3>               
<?php
$row = 1;
foreach ($users as $key => $user) {
?>
                    <div class="login-activity"><?=$row?> . <?= $user['f_name']?>  <?=$user['l_name'] ?> logged in at <?= $user['recent_login'] ?></div>                
<?php
    $row++;
}
?>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
<script>
var botmanWidget = {
frameEndpoint: '/chat/botman-widget.html', // Make sure this is correct
    chatServer: '/chat/chatbot.php',
    introMessage: "👋 Hello! I'm Chatbot. Ask me anything!",
    title: "Chatbot",
    mainColor: "#ADD8E6",
    bubbleBackground: "#ADD8E6",
    placeholderText: "Type your question here..."
};
</script>

<script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
<?php
?>
