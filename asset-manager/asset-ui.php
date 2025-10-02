<?php
include_once '../config.php';
check_auth();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Asset Tracking</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
    <script type="text/javascript" src="asset-ajax.js"></script>
    <link rel="stylesheet" href="manager.css">
    <?php include_once '../navbar.php'; ?>
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
                        <input type="search" id="profiles" list="profile-list" placeholder="Load Profile" autocomplete="on" accept="text/plain">
                        <datalist id="profile-list">
                            <?php
                            $query = "SELECT distinct profile_name FROM user_asset_profile WHERE email = :email";
                            $query_stmt = $dbh->prepare($query);
                            $query_stmt->execute([":email" => $_SESSION['email']]);
                            $result = $query_stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
                            foreach ($result as $row) {
                                echo "<option value='" . htmlspecialchars($row['profile_name']) . "' data-id='" . htmlspecialchars($row['profile_name']) . "'>";
                            }
                            ?>
                        </datalist>
                        <button id="load-profile">Load Profile</button>
                    </li>

                </ul>
            </nav>
            <div class="quick-buttons">
                <input type="search" id="dept" list="dept-list" placeholder="Department" autocomplete="on" accept="text/plain">
                <datalist id="dept-list">
                    <?php
                    $query = "SELECT dept_name FROM department";
                    $result = $dbh->query($query);
                    foreach ($result as $row) {
                        echo "<option value='" . htmlspecialchars($row['dept_name']) . "' data-id='" . htmlspecialchars($row['dept_name'])  . "'>";
                    }
                    ?>
                </datalist>
                <a class="cta" href="#"><button id="quick-start" name="quick-start">Quick Start</button></a>
            </div>
            <div class="downloads">
                <a class="cta" href="#"><button id="restart" name="restart">Restart Sheet</button></a>

            </div>
        </header>
        <nav id="nav-ui2" style="place-self: center;">
            <ul class="nav-ui">
                <li>
                    <input type="search" id="search-db" list="asset-list" placeholder="Search Assets" autocomplete="off" accept="text/plain">
                    <datalist id="asset-list">
                        <?php
                        $query = "SELECT asset_tag FROM asset_info WHERE asset_status = 'In Service'";
                        $result = $dbh->query($query);
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
