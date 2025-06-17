<?php
include_once("../config.php");
if (!isset($_SESSION['role'])) {
    header("Location: https://dataworks-7b7x.onrender.com/auth/login.php");
    exit();
}
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
    #dept_id_search,
    #bldg-id-search,
    #status-filter,
    #price-value,
    #price-filter,
    .filter-select {
        font-size: calc(1.0vh + 0.4vw);
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        cursor: pointer;
    }
    #price-filter:focus,
    #status-filter:focus,
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
$category = isset($_GET['categories']) ? $_GET['categories'] : 'assets';
$status = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : 'In Service';
if ($search === NULL) {
    $offset = 1;
}
?>
<!-- Search box. -->
<div class="asset-search">
<input class = "search-input" type="hidden" name="offset" id="offset" value="<?=$offset?>">
<input class="search-input" type="text" name="search" id="search" value="<?=$search?>" placeholder="Search for an asset..." style="width: 60%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
 <select class="filter-select"  name="categories" id="categories">
                <option value="assets">Assets</option>
                <option value="buildings">Buildings</option>
                <option value="departments">Departments</option>
            </select>
            <br>
            <input type="number" class="filter-bldg" id="bldg-id-search" name="bldg-id-search" placeholder="Enter Building ID" />

            <select class="filter-assets" name="statusFilter" id="status-filter">
                    <option value="all">All Status</option>
                    <option value="service">In Service</option>
                    <option value="disposed">Disposed</option>
                </select>

            <select class="filter-assets" name="price-operator" id="price-filter">
                <option value=">">&gt;</option>
                <option value="<">&lt;</option>
                <option value="=">=</option>
                <option value=">=">&ge;</option>
                <option value="<=">&le;</option>
            </select>
            <input type="number" class="filter-assets" id="price-value" name="price-value" placeholder="Enter price" />
            <input type="text" class="filter-assets" id="dept-id-search" name="dept-id-search" placeholder="Enter Department ID" />
            <button class="search-button" id="search-btn">Search</button>
<br>
<label for="asset_name" class="filter-assets" id="asset_name_label">Asset Name<input type="checkbox" id="asset_name" value="asset_name" name="asset_name"> </label>
            <label for="dept_id" class="filter-assets" id="dept_id_label">Department ID<input type="checkbox" id="dept_id" value="dept_id" name="dept_id"> </label>
            <label for="room_tag"class="filter-assets"  id="room_tag_label">Room Tag<input type="checkbox" id="room_tag" value="room_tag" name="room_tag"> </label>
            <label for="room_loc" id="room_loc_label">Room Number<input type="checkbox" id="room_loc" value="room_loc" name="room_loc"> </label>
            <label for="asset_sn" class="filter-assets" id="asset_sn_label">Serial Number<input type="checkbox" id="asset_sn" value="asset_sn" name="asset_sn"> </label>
            <label for="asset_price" class="filter-assets" id="asset_price_label">Cost<input type="checkbox" id="asset_price" value="asset_price" name="asset_price"> </label>
            <label for="asset_po" class="filter-assets" id="asset_po_label">Purchase Order<input type="checkbox" id="asset_po" value="asset_po" name="asset_po"> </label>

            <label for="bldg_id" class="filter-bldg" id="bldg_id_label">Building ID<input type="checkbox" id="bldg_id" value="bldg_id" name="bldg_id"> </label>
            <label for="bldg_name" class="filter-bldg" id="bldg_name_label">Building Name<input type="checkbox" id="bldg_name" value="bldg_name" name="bldg_name"> </label>
        </div>
  <br>
  <br />
  <!-- Suggestions will be displayed in below div. -->
  <div id="display"></div>
</body>
</html>
