<?php
//Including Database configuration file.
include_once "../config.php";
//Getting value of "search" variable from "script.js".
if (isset($_POST['search'])) {
//Search box value assigning to $Name variable.
  $tag = $_POST['search'];
//Search query.
  $query = "SELECT asset_tag FROM asset_info WHERE asset_tag LIKE '%$tag%' LIMIT 5";
//Query execution
  $exec_query = $dbh->prepare($query);
  $exec_query->execute([$tag]);
  $result = $exec_query->fetchAll(PDO::FETCH_ASSOC);
//Creating unordered list to display result.
  echo '
<ul>
  ';
 //Fetching result from database.
  foreach ($result as $key => $row ) {     ?>
  <!-- Creating unordered list items.
       Calling javascript function named as "fill" found in "script.js" file.
       By passing fetched result as parameter. -->
  <li onclick='fill("<?php echo $row['asset_tag']; ?>")'>
  <a>
  <!-- Assigning searched result in "Search box" in "search.php" file. -->
      <?php echo $row['asset_tag']; ?>
  </li></a>
  <!-- Below php code is just for closing parenthesis. Don't be confused. -->
  <?php
}}
?>
</ul>
