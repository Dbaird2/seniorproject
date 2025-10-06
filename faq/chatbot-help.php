<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Visual Guide - Help Center'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8fafc;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #003DA5 0%, #0052CC 100%);
            padding: 40px 20px;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #FFB81C;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            color: #ffffff;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .progress-bar {
            background: white;
            border-radius: 25px;
            padding: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .progress-fill {
            background: linear-gradient(90deg, #FFB81C, #FFA500);
            height: 8px;
            border-radius: 20px;
            transition: width 0.3s ease;
        }

        .step-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .step-counter {
            background: #003DA5;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
        }

        .nav-btn {
            background: #FFB81C;
            color: #003DA5;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-btn:hover {
            background: #FFA500;
            transform: translateY(-1px);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .step-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none;
        }

        .step-container.active {
            display: block;
        }

        .step-title {
            color: #003DA5;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-number {
            background: #003DA5;
            color: #FFB81C;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .step-description {
            font-size: 1.1rem;
            color: #37474f;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .image-container {
            margin: 25px 0;
            text-align: center;
        }

        .step-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: 3px solid #FFB81C;
        }

        .image-caption {
            margin-top: 10px;
            font-style: italic;
            color: #546e7a;
            font-size: 0.95rem;
        }

        .highlight-box {
            background: #e8f5e8;
            border-left: 4px solid #4caf50;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }

        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }

        .tip-box {
            background: #FFF8E1;
            border-left: 4px solid #FFB81C;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }

        .action-list {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .action-list h4 {
            color: #003DA5;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .action-list ol {
            padding-left: 20px;
        }

        .action-list li {
            margin-bottom: 8px;
            color: #37474f;
        }

        .completion-section {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-top: 30px;
        }

        .completion-section h3 {
            color: #2e7d32;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .step-navigation {
                flex-direction: column;
                gap: 15px;
            }

            .step-container {
                padding: 20px;
            }

            .step-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<?php $guide_title = 'How to Add Profiles';  $guide_description = 'Step-by-step visual instruction to help you set up your profiles.'; ?>
    <div class="container">
        <div class="header">
            <h1><?php echo isset($guide_title) ? $guide_title : 'Visual Process Guide'; ?></h1>
            <p><?php echo isset($guide_description) ? $guide_description : 'Step-by-step visual instructions to help you complete this process successfully.'; ?></p>
        </div>

        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>

        <!-- Step Navigation -->
        <div class="step-navigation">
            <div class="step-counter">
                Step <span id="currentStep">1</span> of <span id="totalSteps">3</span>
            </div>
            <div class="nav-buttons">
                <button class="nav-btn" id="prevBtn" onclick="changeStep(-1)" disabled>Previous</button>
                <button class="nav-btn" id="nextBtn" onclick="changeStep(1)">Next</button>
            </div>
        </div>

         <div class="step-container active" data-step="1">
            <div class="step-title">
                <div class="step-number">1</div>
                Locating Chatbot
            </div>
            <div class="step-description">
                Chatbot will always be on the bottom right of the page. Note Chatbot is not on every page.
            </div>

            <div class="image-container">
                <img src="../pictures/profile-pics/chatbot1.png" alt="Getting started with chatbot1" class="step-image">
                <div class="image-caption">Example: Chatbot Icon</div>
            </div>
            <div class="image-container">
                <img src="../pictures/profile-pics/chatbot2.png" alt="Getting started with chatbot2" class="step-image">
                <div class="image-caption">Example: Chatbot Chatbox</div>
            </div>
        </div>
        <div class="step-container active" data-step="2">
            <div class="step-title">
                <div class="step-number">2</div>
                    Searching Assets
            </div>
            <div class="step-description">
                Chatbot is caps insensitive. Meaning: asset 12345 and ASSET 12345 will both search for asset 12345.
            </div>

            <div class="image-container">
                <img src="../pictures/profile-pics/asset-searching.png" alt="Searching assets" class="step-image">
                <div class="image-caption">Example: Chatbot Asset Searching</div>
            </div>
        </div>
        <div class="step-container" data-step="3">
            <div class="step-title">
                <div class="step-number">3</div>
                Ticketing System
            </div>
            <div class="step-description">
                Here is how to submit a ticket in our system.
            </div>

            <div class="image-container">
                <img src="../pictures/profile-pics/ticket-start.png" alt="Getting started with tickets" class="step-image">
                <div class="image-caption">Example: Start of Ticketing</div>
            </div>
            <div class="image-container">
                <img src="../pictures/profile-pics/ticket-sent.png" alt="Ticket Sent" class="step-image">
                <div class="image-caption">Example: Start of Submitting</div>
            </div>
            <div class="image-container">
                <img src="../pictures/profile-pics/showing-tickets.png" alt="Admin Ticket View" class="step-image">
                <div class="image-caption">Example: Ticket Submission Viewing</div>
            </div>
           
        </div>
       
    <script>
        let currentStepNum = 1;
        const totalSteps = 3;

        function updateProgress() {
            const progress = (currentStepNum / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('currentStep').textContent = currentStepNum;
            document.getElementById('totalSteps').textContent = totalSteps;
        }

        function updateButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            prevBtn.disabled = currentStepNum === 1;
            nextBtn.disabled = currentStepNum === totalSteps;

            if (currentStepNum === totalSteps) {
                nextBtn.textContent = 'Complete';
            } else {
                nextBtn.textContent = 'Next';
            }
        }

        function showStep(stepNum) {
            // Hide all steps
            document.querySelectorAll('.step-container').forEach(step => {
                step.classList.remove('active');
            });

            // Show current step
            document.querySelector(`[data-step="${stepNum}"]`).classList.add('active');

            updateProgress();
            updateButtons();
        }

        function changeStep(direction) {
            const newStep = currentStepNum + direction;

            if (newStep >= 1 && newStep <= totalSteps) {
                currentStepNum = newStep;
                showStep(currentStepNum);
            }
        }

        // Initialize
        updateProgress();
        updateButtons();

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' && currentStepNum > 1) {
                changeStep(-1);
            } else if (e.key === 'ArrowRight' && currentStepNum < totalSteps) {
                changeStep(1);
            }
        });
    </script>
</body>
</html>
