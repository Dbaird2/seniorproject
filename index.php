<?php
session_start();

if (!isset($_SESSION['role'])) {
include_once 'navbar.php';

$query = "SELECT * FROM asset_info join asset_bus_change join asset_tag_change join asset_room_change
WHERE timestamp < NOW() - INTERVAL 1 WEEK
ORDER BY timestamp DESC LIMIT 5";

$user_query = "SELECT * FROM user_table ORDER BY last_login DESC LIMIT 5";
try {
    $stmt = $dbh->prepare($query);
    $stmt_user = $dbh->prepare($user_query);

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
} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    li {
        list-style: none;
        margin: 0.5vh 0;
    }
    a {
        text-decoration: none;
        color: black;
    }
    body {
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
    .asset-search {
        margin-top: 2vh;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        background-color: white;
        border: lightgray 1px solid;
        border-radius: 30px;
        margin: 0 auto;
        padding: 1vh 2vw;
        width: 90vw;
    }
    #submit-btn:hover {
        transform: translateY(-2px);
    }
    .search-input {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: calc(1.0vh + 0.4vw);
        transition: border-color 0.3s ease;
        background: #f8fafc;
    }
    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
    }
    .filter-select {
        font-size: calc(1.0vh + 0.4vw);
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
    }

    .search-button {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        font-size: calc(1.0vh + 0.4vw);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
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
</style>
<body>
    <section class="container">
        <div class="container-head">
            <div class="head-text">
                <h2>Asset Management Dashboard</h2>
                <p>Monitor and manage your organization's assets in real-time</p>
            </div>
            <div class="head-bot">
            
            
                <div class="small-box">
                    <span class="small-box-text"><?php $number ?>12,456<br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Total Assets</strong></span>
                </div>
                <div class="small-box">
                    <span class="small-box-text"><?php $number ?>19<br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Weekly Change(s)</strong></span>
                </div>
                <div class="small-box">
                    <span class="small-box-text"><?php $number ?>1,346<br></span>
                    <span style="font-size: calc(0.7vh + 0.4vw);"><strong>Weekly Add(s)</strong></span>
                </div>
            </div>
        </div>
        <br>
        <div class="asset-search">
            <input class="search-input" type="text" name="search" placeholder="Search for an asset..." style="width: 60%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
            <select class="filter-select" name="categories" id="categories">
                <option value="all">All Categories</option>
                <option value="electronics">Electronics</option>
                <option value="vehicles">Vehicles</option>
                <option value="equipment">Equipment</option>
            </select>
            <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="service">In Service</option>
                    <option value="disposed">Disposed</option>
                </select>
            <button class="search-button" onclick="performSearch()">Search</button>
        </div>
        <div class="recent-asset-change">
            <div class="recent-assets">
                <?php
                    // REPLACE TAG, STATUS, ETC WITH $assets[0]['asset_tag'], 
                    // $assets[0]['status'], ETC WHEN DB DATA IS AVAILABLE
                ?>
                <h3>Recent Asset Additions</h3>
                <div class="asset-item asset-even asset-header">
                    <div class="asset-id">Asset Tag</div>
                    <div class="asset-status">Status</div>
                    <div class="asset-category">Category</div>
                    <div class="asset-location">Location</div>
                    <div class="asset-price">Price</div>
                    <div class="asset-price">Custodian</div>

                </div>
                <div class="asset-item asset-odd">
                    <div class="asset-id">67890</div>
                    <div class="asset-status">Disposed</div>
                    <div class="asset-category">Electronics</div>
                    <div class="asset-location">Warehouse A</div>
                    <div class="asset-price">$500</div>
                    <div class="asset-price">Bob</div>
                </div>
                <div class="asset-item asset-even">
                    <div class="asset-id">67890</div>
                    <div class="asset-status">Disposed</div>
                    <div class="asset-category">Electronics</div>
                    <div class="asset-location">Warehouse A</div>
                    <div class="asset-price">$500</div>
                    <div class="asset-price">Bob</div>
                </div>
                <div class="asset-item asset-odd">
                    <div class="asset-id">67890</div>
                    <div class="asset-status">Disposed</div>
                    <div class="asset-category">Electronics</div>
                    <div class="asset-location">Warehouse A</div>
                    <div class="asset-price">$500</div>
                    <div class="asset-price">Bob</div>
                </div>
                <div class="asset-item asset-even">
                    <div class="asset-id">67890</div>
                    <div class="asset-status">Disposed</div>
                    <div class="asset-category">Electronics</div>
                    <div class="asset-location">Warehouse A</div>
                    <div class="asset-price">$500</div>
                    <div class="asset-price">Bob</div>
                </div>
                <div class="asset-item asset-odd">
                    <div class="asset-id">67890</div>
                    <div class="asset-status">Disposed</div>
                    <div class="asset-category">Electronics</div>
                    <div class="asset-location">Warehouse A</div>
                    <div class="asset-price">$500</div>
                    <div class="asset-price">Bob</div>
                </div>
            </div>
            <div class="activity">
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <ul>
                        <li><a href="#add_asset.php">➕ Add Asset</a></li>
                        <li><a href="auditing.php">Start Audit</a></li>
                        <li><a href="#change_asset_tag.php">Change Asset Tags</a></li>
                        <li><a href="help.php">Help</a></li>
                    </ul>

                </div>
                <div class="recent-activity">
                    <h3>Recent Activity</h3>
                    <ul>
                        <li>User Donald Trump logged in</li>
                    </ul>

                </div>
            </div>


        </div>
    </section>
    
</body>
</html>
<?php
} else {
    #header("Location: localhost:3000/login.php");
    exit();
}