<?php
include_once("../config.php");
include_once("../navbar.php");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Asset Management</title>
  <!-- Including jQuery is required. -->
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
   <script src="https://code.jquery.com/jquery-3.5.1.min.js"
     integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
     crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="script.js"></script>
</head>
<style>

    body {
            margin: 0;
            height: 100vh;
            font-size: calc(0.5vw + 0.4vh);
            width: 100%;
            position: absolute;
            top: 8vh;
    }
    .asset-search {
        margin-top: 2vh;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        background-color: white;
        border: lightgray 1px solid;
        border-radius: 30px;
        margin: 0 auto;
        padding: 1vh 2vw;
        width: 85vw;
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
</style>
<body>
<?php
$search = isset($_GET['search']) ? $_GET['search'] : NULL;
$offset = isset($_GET['offset']) ? $_GET['offset'] : 1;
if ($search === NULL) {
    $offset = 1;
}
?>
<!-- Search box. -->
<div class="asset-search">
<input class = "search-input" type="hidden" name="offset" id="offset" value="<?=$offset?>">
<input class="search-input" type="text" name="search" id="search" onchange=<?php $offset=1?> value="<?=$search?>" placeholder="Search for an asset..." style="width: 60%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
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
  <br>
  <br />
  <!-- Suggestions will be displayed in below div. -->
  <div id="display"></div>
</body>
</html>
