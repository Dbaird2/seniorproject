<?php
error_reporting(0);
require_once 'config.php';
if (isset($_SESSION['role'])) {
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
<style>
    * {
        margin:0;
     }
    .is-index li {
        list-style: none;
        margin: 0.5vh 0;
    }
    .is-index a {
        text-decoration: none;
        color: black;
    }
    .is-index {
        position: relative;
        top: 5vh;
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    .container {    
        width: 90vw;
        margin: 0 auto;
        padding: -1vh 0;

    }

    .small-box {
        background-color: rgb(129, 195, 228);
        margin: 0.5vw;
        height:10vh;
        width: 33vw;
        color: white;
        border-radius:30px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
        padding: 1vh;
    }
    .small-box-text {
        font-size: calc(1vw + 1.5vh);
        justify-content: center;
        font-weight: bold; 
    }
    .container-head {
        background-image: linear-gradient(to right, rgb(43, 158, 216) ,rgb(129, 195, 228));
        border: lightgray 1px solid;
        border-radius: 30px;
        margin: 0 auto;
        radius: 10px;
        width: 90vw;       
        box-shadow: 1px 2px 5px rgba(0, 0, 0, 0.1);
    }
    .head-text {
        color: white;
        padding: 2vh 2vw;
        font-size: calc(0.4vw + 0.9vh);
        font-weight: bold;
    }
    .head-bot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1vh 2vw;
        align-items:end;
    }
    .head-bot-text {
        font-size:calc(1vh+2vw);
    }
    .recent-asset-change {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .recent-assets {
        background-color: white;
        border: lightgray 1px solid;
        border-radius: 30px;
        padding: 1vh 1vw;
        margin-top: 2vh;
        margin-right: 1vw;
        width: 50vw;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .quick-actions {
        background-color: white;
        border: lightgray 1px solid;
        border-radius: 30px;
        padding: 1vh 1vw;
        margin-top: 2vh;
        width: 40vw;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

    }
    .recent-activity {
        background-color: white;
        border: lightgray 1px solid;
        border-radius: 30px;
        padding: 1vh 1vw;
        margin-top: 2vh;
        width: 40vw;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .asset-item {
        margin-bottom: 0.5vh;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
        border: 1px solid white;
        border-radius: 10px;
    }
    .asset-item > div {
        color: rgb(88, 88, 88);
        font-size: calc(1vh + 0.4vw);
        text-align: center;
    }
    .asset-odd {
        background-color: rgb(255, 255, 255);
    }
    .asset-even {
        background-color: rgb(230, 247, 252);
    }
    .asset-header {
        font-weight: bold;
        color: black;
    }
    .option {
        text-align: left;
        background-color: rgb(249, 249, 249);
        color: white;
        padding: 1vh 2vw;
        border-radius: 10px;
        margin-bottom: 1vh;
        width: 90%;
        font-size: calc(0.7vh + 0.4vw);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .login-activity {
        text-align: left;
        background-color: rgb(249, 249, 249);
        color: black;
        padding: 1vh 2vw;
        border-radius: 10px;
        margin-bottom: 1vh;
        width: 90%;
        font-size: calc(0.7vh + 0.4vw);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>
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

<script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>}
<?php
} else {
    header("Location: https://dataworks-7b7x.onrender.com/auth/login.php");
    exit();
}
?>
