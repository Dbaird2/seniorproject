<?php
include_once '../config.php';
check_auth();

include_once '../navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
    <script type="text/javascript" src="asset-ajax.js"></script>
    <link rel="stylesheet" href="manager.css">
</head>
<style>
    * {
        margin: 0;
    }
</style>

<body>
    <section class="is-manager">
        <header>
            <nav id="nav-ui">
                <ul class="nav-ui">
                    <li>
                        <input type="text" id="profiles" list="profile-list" placeholder="Load Profile" autocomplete="on" accept="text/plain">
                        <datalist id="profile-list">
                            <?php
                            $query = "SELECT profile_name FROM user_asset_profile WHERE email = :email";
                            $query = $dbh->prepare($query);
                            $query_stmt->execute([":email" => $_SESSION['email']]);
                            $result = $query_stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
                            /*
                            test data
                            $result = [
                                ["profile_id" => "1", "profile_name" => "D21200"],
                                ["profile_id" => "2", "profile_name" => "D21500"]
                            ];
                            */
                            foreach ($result as $row) {
                                echo "<option value='" . htmlspecialchars($row['profile_name']) . "' data-id='" . htmlspecialchars($row['profile_id']) . ' ' . htmlspecialchars($row['profile_name']) . "'>";
                            }
                            ?>
                        </datalist>
                        <button id="load-profile">Load Profile</button>
                    </li>

                </ul>
            </nav>
            <div class="quick-buttons">
                <input type="text" id="dept" list="dept-list" placeholder="Department" autocomplete="on" accept="text/plain">
                <datalist id="dept-list">
                    <?php
                    $query = "SELECT dept_name FROM department";
                    $result = $dbh->query($query);
                    /*
                    test data
                    $result = [
                        ["dept_name" => "DISTRIBUTION"],
                        ["dept_name" => "FACILITIES"]
                    ];
                    */
                    foreach ($result as $row) {
                        echo "<option value='" . htmlspecialchars($row['dept_name']) . "' data-id='" . htmlspecialchars($row['dept_name'])  . "'>";
                    }
                    ?>
                </datalist>
                <a class="cta" href="#"><button id="quick-start" name="quick-start">Quick Start</button></a>
            </div>
            <div class="downloads">
                <a href="#"><button>Excel Sheet</button></a>
                <a href="#"><button>PDF</button></a>
                <a class="cta" href="#"><button id="restart" name="restart">Restart Sheet</button></a>

            </div>
        </header>
        <nav id="nav-ui" style="place-self: center;">
            <ul class="nav-ui">
                <li>
                    <input type="text" id="search-db" list="asset-list" placeholder="Search Assets" autocomplete="off" accept="text/plain">
                    <datalist id="asset-list">
                        <?php
                        $query = "SELECT asset_tag FROM asset_info WHERE asset_status = 'active' ORDER BY asset_name";
                        $result = $dbh->query($query);
                        // test data
                        /*$result = [
                            ["asset_id" => "A12345", "asset_name" => "Laptop"],
                            ["asset_id" => "A67890", "asset_name" => "Desktop"]
                        ];
                         */
                        foreach ($result as $row) {
                            echo "<option value='" . htmlspecialchars($row['asset_tag']) . "' data-id='" . htmlspecialchars($row['asset_tag']) . "'>";
                        }
                        ?>
                    </datalist>

                    <button id="add-asset">Add Asset</button>
                </li>
                <li>


                </li>
            </ul>
        </nav>

        <div id="display-table"></div>
    </section>

    </div>


</body>

</html>
