<?php include_once("../../config.php"); 
check_auth();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
    <script type="text/javascript" src="manage-profile-ajax.js"></script>
    <link rel="stylesheet" href="manage-profile.css">
    <?php include_once('../../navbar.php'); ?>
    <style>
        * {
            margin: 0;
        }
    </style>
</head>
<body>
    <nav class="is-manage-profile">
        <section class="page1">
            <h2 style="text-align:center;">Profile Management</h2>
            <header>
                <input type="text" id="display-name" placeholder="Profile Name">
                <button>Add Profile</button>
                <button>Delete Profile</button>
            </header>
        </section>
        <div class="page2">
            <h2 style="text-align:center;">Your Profiles</h2>

            <div id="display-profiles"></div>
        </div>
    </nav>
    <script>
        displayProfiles();
    </script>
</body>

</html>
