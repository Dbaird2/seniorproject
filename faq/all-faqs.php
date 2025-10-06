<?php include_once("../config.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Help Pages</title>
    <link rel="stylesheet" href="help-page.css">
    <?php include_once("../navbar.php"); ?>
</head>
<body>
    <div class="page-wrapper">
        <div class="help-container">
            <h1 class="help-title">Help & Documentation</h1>
            <p class="help-subtitle">Select a topic to learn more</p>
            
            <div class="help-cards">
                <a href="audit-help.php" target="_blank" class="help-card">
                    <div class="help-card-icon">📋</div>
                    <h2>Auditing Help</h2>
                    <p>Learn how to conduct audits, manage audit data, and generate reports</p>
                </a>
                
                <a href="profile-help.php" target="_blank" class="help-card">
                    <div class="help-card-icon">👤</div>
                    <h2>Profile Help</h2>
                    <p>Manage user profiles, permissions, and account settings</p>
                </a>
                
                <a href="chatbot-help.php" target="_blank" class="help-card">
                    <div class="help-card-icon">💬</div>
                    <h2>Chatbot Help</h2>
                    <p>Get assistance using the chatbot feature and AI-powered support</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

