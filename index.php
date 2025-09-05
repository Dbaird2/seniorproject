<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSUB IMS</title>
</head>
<style>
    body,
    html {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
    }

    video {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
        /* Send the video to the background */
    }

    .content {
        position: relative;
        z-index: 1;
        color: white;
        text-align: center;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
</style>

<body>
    <video autoplay muted loop>
        <source src="videos/runners_rise.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="content">
        <h1>Welcome</h1>
        <p><a href="auth/login.php">Click Here</a> to login.</p>
    </div>
</body>

</html>
