<?php
include_once "config.php";

if (isset($_POST['search'])) {
    $tag = $_POST['search'];

    $query = "SELECT asset_tag FROM asset_info WHERE asset_tag LIKE :tag LIMIT 5";
    $exec_query = $dbh->prepare($query);
    $exec_query->execute(['tag' => "%$tag%"]);
    $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo '<ul>';
        foreach ($result as $row) {
            // Escape values for safety
            $safe_tag = htmlspecialchars($row['asset_tag'], ENT_QUOTES);
            echo "
                <li onclick='fill(\"$safe_tag\")'>
                    <a>$safe_tag</a>
                </li>
            ";
        }
        echo '</ul>';
    }
}
?>