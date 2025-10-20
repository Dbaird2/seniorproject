<?php include_once("../config.php");
check_auth('high');
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>add-asset.php</title>
<style>
        body {
                margin: 0;
        }

        .has_asset {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                min-height: 100vh;
                width: 100%;
        }

        .container {
                width: 100%;
                display: flex;
                flex-direction: inline-block;
                color: lightblue;
                justify-content: center;
        }

        .middle {
                display: flex;
                flex-direction: column;
                justify-content: center;
        }

        .container button {
                width: 50%;
                padding: 16px;
                background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                letter-spacing: 0.5px;
                text-transform: uppercase;
                overflow: hidden;
                align-items: center;
                justify-content: center;
        }

        .container>div {}

        .container input {}

        .container label {
                padding: 5px 5px;
                color: black;
        }

        .form {
                display: flex;
                justify-content: center;

        }

        .form-header {
                background: linear-gradient(180deg, #1976d2 0%, #2196f3 100%);
                color: white;
                text-align: center;
                padding: 30px 20px;
                position: relative;
                border-radius: 8px;
                width: 20%;
        }

        .bigger-input {
                height: 5vh;
                resize: vertical;
                padding: 10px;
        }

        @media (max-width: 480px) {
                .sub-body {
                        margin: 10px;
                        max-width: none;
                }

                .login-body {
                        padding: 30px 20px 20px;
                }

                .form-header {
                        padding: 25px 20px;
                }

                .form-header h1 {
                        font-size: 24px;
                }
        }

        .btn-container {
                display: flex;
                justify-content: center;
        }

        .input-container {
                padding: 5px 10px;
        }

        .input {
                font-size: 1em;
                padding: 0.6em 1em;
                border: none;
                border-radius: 6px;
                background-color: #f8f8f8;
                max-width: 100%;
                color: #333;
        }
</style>
<?php
$query = "SELECT DISTINCT bldg_name FROM bldg_table";
$stmt = $dbh->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT DISTINCT dept_id FROM deptartment";
$stmt = $dbh->prepare($query);
$stmt->execute();
$dept_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
/*$result = [["dept_id" => "D21560", "bldg_name" => "DISTRIBUTION"]];*/
?>

<body>
        <?php include_once("../navbar.php"); ?>

        <div class="has_asset">
                <div class="form">
                        <h1 class='form-header'>Asset Addition Form</h1>
                </div>
                <div class="container">
                        <div class="middle">
                                <form action="insert-asset.php" method="post" name="add">
                                        <div class="input-container">
                                                <label for="tag">Asset Tag</label>
                                                <input type="text" name="tag" class="input" placeholder="Ex: 12345" required>
                                        </div>
                                        <div class="input-container">
                                                <label for="descr">Description</label>
                                                <input type="text" name="descr" class="input" placeholder="Ex: DELL LATITUDE 5450" required>

                                        </div>
                                        <div class="input-container">
                                                <label for="type2">Type</label>
                                                <select name="type2" class="input" required>
                                                    <option value="Laptop">Laptop</option>
                                                    <option value="Tablet">Tablet</option>
                                                    <option value="Desktop">Desktop</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                        </div>
                                        <div class="input-container">
                                                <label for="type">Asset Category</label>
                                                <select name="type" class="input" required>
                                                    <option value="Equipment">Equipment</option>
                                                    <option value="Property">Property</option>
                                                    <option value="Fleet">Fleet</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                        </div>
                                        <div class="input-container">
                                                <label for="acq">Acquisiton Date: </label>
                                                <input type="date" name="acq" class="input" required>

                                        </div>
                                        <div class="input-container">
                                                <label for="sn">Serial Number</label>
                                                <input type="text" name="sn" class="input" placeholder="Ex: 7BXS36">

                                        </div>
                                        <div class="input-container">
                                                <label for="model">Model</label>
                                                <input type="text" name="model" class="input" placeholder="Ex: DELL">
                                        </div>
                                        <div class="input-container">
                                                <label for="make">Make</label>
                                                <input type="text" name="make" class="input" placeholder="Ex: LATITUDE">
                                        </div>
                                        <div class="input-container">
                                                <label for="cost">Total Cost</label>
                                                <input type="number" name="cost" class="input" placeholder="Ex: 550" required>

                                        </div>
                                        <div class="input-container">
                                                <label class="" for="dept-id">Department ID</label>
                                                <input class="input" placeholder="Department ID" type="search" name="dept-id" id="dept-id" list="dept-ids" autocomplete="off">
                                                <datalist id="dept-ids">
                                                        <?php foreach ($dept_result as $dept) { ?>
                                                                <option value="<?= $dept["dept_id"] ?>"><?= $dept["dept_id"] ?></option>
                                                        <?php } ?>
                                                </datalist>
                                        </div>
                                        <div class="input-container">
                                                <label for="bldg-name">Building Name</label>
                                                <input class="input" placeholder="Building Name" type="search" name="bldg-name" id="bldg-name" list="bldg-names" autocomplete="off">
                                                <datalist id="bldg-names">
                                                        <?php foreach ($result as $bldg) { ?>
                                                                <option value="<?= $bldg["bldg_name"] ?>"><?= $bldg["bldg_name"] ?></option>
                                                        <?php } ?>
                                                </datalist>
                                        </div>
                                        <div class="input-container">
                                                <label for="room-loc">Room Name/Number</label>
                                                <input type="text" name="room-loc" class="input" placeholder="Room Name/Number">
                                        </div>
                                        <div class="input-container">
                                                <label for="profile">Profile ID</label>
                                                <input type="text" name="profile" class="input" placeholder="Profile ID" required>

                                        </div>
                                        <div class="input-container">
                                                <label for="po">Purchase Order</label>
                                                <input type="text" name="po" class="input" placeholder="Purchase Order" required>

                                        </div>
                                        <div class="btn-container">
                                                <button name="add">Submit</button>
                                        </div>
                                </form>
                        </div>
                </div>
        </div>
</body>

</html>
