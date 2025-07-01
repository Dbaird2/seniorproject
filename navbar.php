<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
body,h1,h2,h3,h4,h5,h6 {font-family: "Lato", sans-serif}
.w3-bar,h1,button {font-family: "Montserrat", sans-serif}
.fa-anchor,.fa-coffee {font-size:200px}

</style>

<!-- Navbar -->
<div class="screen-size w3-animate-opacity w3-top" >
  <div class="w3-bar w3-cobalt w3-card w3-small">
    <a class="w3-bar-item w3-button w3-hide-small w3-hide-small w3-right w3-padding-small w3-hover-white w3-small w3-cobalt" href="javascript:void(0);" onclick="myFunction()" title="Toggle Navigation Menu"><i class="fa fa-bars"></i></a>
    <a href="https://dataworks-7b7x.onrender.com/index.php" class="w3-bar-item w3-button w3-hover-white w3-padding-small w3-left-align">Home</a>
<?php
if (isset($_SESSION['id'])) {
?>
    <a href="https://dataworks-7b7x.onrender.com/audit/auditing.php" class="w3-bar-item w3-button w3-hide-small w3-padding-small w3-hover-white  w3-left-align">Start an Audit</a>
    <a href="https://dataworks-7b7x.onrender.com/search/search.php" class="w3-bar-item w3-button w3-hide-small w3-padding-small w3-hover-white  w3-left-align">Search Assets</a>
    <a href="https://dataworks-7b7x.onrender.com/change_asset_tag.php" class="w3-bar-item w3-button w3-hide-small w3-padding-small w3-hover-white  w3-left-align">Change Asset Tags</a>
    <a href="https://dataworks-7b7x.onrender.com/auth/logout.php" class="w3-bar-item w3-button w3-padding-small w3-white w3-right-align w3-right">Logout</a>
<?php
} else {
?>
    <a href="https://dataworks-7b7x.onrender.com/auth/login.php" class="w3-bar-item w3-button w3-padding-small w3-white w3-right-align w3-right">Login</a>
<?php
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
?>
    <a href="https://dataworks-7b7x.onrender.com/auth/signup.php" class="w3-bar-item w3-button w3-padding-small w3-right-align w3-right">Signup</a>
<?php
  }
?>
   </div>
  <div id="navDemo" class="w3-sidebar w3-hover-cobalt w3-light-gray w3-hide w3-hide-small w3-bar-block w3-hide-small w3-small w3-animate-right" style="height:7vh;width:6vw;right:0">
    <a href="https://dataworks-7b7x.onrender.com/audit/auditing.php" class="w3-bar-item w3-button w3-padding-small">Start an Audit</a>
    <a href="https://dataworks-7b7x.onrender.com/help.php" class="w3-bar-item w3-button w3-padding-small">Help</a>
  </div>
</div>


<script>
// Used to toggle the menu on small screens when clicking on the menu button
function myFunction() {
  var x = document.getElementById("navDemo");
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
  } else {
    x.className = x.className.replace(" w3-show", "");
  }
}

</script>
