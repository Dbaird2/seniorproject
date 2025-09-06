<?php
include_once("../config.php");
session_destroy();
session_unset();

header("location: https://dataworks-7b7x.onrender.com/index.php");
exit();
?>
