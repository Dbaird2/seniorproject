<?php
error_reporting(0);
require_once 'config.php';
check_auth();
include_once 'navbar.php';
$query = "SELECT * FROM asset_info NATURAL JOIN room_table
    ORDER BY date_added DESC LIMIT 10";

try {
    var_dump($_SESSION['deptid']);
    echo 'dept ' . $_SESSION['deptid'];
} catch (Exception $e) {
    echo $e->getMessage();
}

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
    $asset_type_data_q = "SELECT asset_type, COUNT(*) as count FROM asset_info GROUP BY asset_type";
    $asset_bldg_count_data_q = "SELECT  r.bldg_id, COUNT(*) as count FROM asset_info as a natural join room_table as r natural join bldg_table as b GROUP BY r.bldg_id";
    $type_stmt = $dbh->prepare($asset_type_data_q);
    $type_stmt->execute();
    $data = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
    $type_data = [];
    $type_data[] = ['Asset Type', 'Count'];
    foreach ($data as $row) {
        $type_data[] = [$row['asset_type'], $row['count']];
    }

    $bldg_count_stmt = $dbh->prepare($asset_bldg_count_data_q);
    $bldg_count_stmt->execute();
    $asset_bldg_count_data = $bldg_count_stmt->fetchAll(PDO::FETCH_ASSOC);
    $asset_bldg_count_data_result = [];
    $asset_bldg_count_data_result[] = ['Department ID', 'Asset Count'];
    foreach ($asset_bldg_count_data as $row) {
        $asset_bldg_count_data_result[] = [$row['dept_id'], $row['count']];
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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', {packages: ['corechart']});
google.charts.setOnLoadCallback(drawChart);


function drawChart() {
    var data = google.visualization.arrayToDataTable(<?php echo json_encode($type_data); ?>);

    var options = {
    title: 'Asset Types',
        pieHole: 0.4,
    };

    var chart = new google.visualization.PieChart(document.getElementById('piechart'));

    chart.draw(data, options);
    var data = google.visualization.arrayToDataTable(<?php echo json_encode($asset_bldg_count_data_result); ?>);

    var options = {
    title: 'Asset Count per Department',
        hAxis: {title: 'Department ID'},
        vAxis: {title: 'Asset Count'},
        bar: {groupWidth: '75%'},
        isStacked: true,
        legend: { position: 'top', maxLines: 3 }
    };

    var chart = new google.visualization.ColumnChart(document.getElementById('histogram'));

    chart.draw(data, options);
}
</script>
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
        <section class="graphs">
            <div id="piechart" style="width: 700px; height: 400px;"></div>
            <div id="histogram" style="width: 1300px; height: 400px;"></div>
        </section>
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
localStorage.removeItem('categories');
</script>

<script src="https://cdn.jsdelivr.net/npm/botman-web-widget@0/build/js/widget.js"></script>
<?php
?>
