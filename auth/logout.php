<?php
include_once("../navbar.php");
session_destroy();
session_unset();

header("location: login.php");
exit();
?>
