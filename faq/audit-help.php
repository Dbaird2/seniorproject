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
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 40px 20px;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #1565c0;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            color: #1976d2;
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
            background: linear-gradient(90deg, #42a5f5, #1e88e5);
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
            background: #1976d2;
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
            background: #e3f2fd;
            color: #1565c0;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-btn:hover {
            background: #bbdefb;
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
            color: #1565c0;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-number {
            background: #1976d2;
            color: white;
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
            border: 3px solid #e3f2fd;
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
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
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
            color: #1565c0;
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
<?php $guide_title = 'How to Audit';  $guide_description = 'Step-by-step visual instruction to help you audit your department successfully.'; ?>
    <div class="container">
         Header Section 
        <div class="header">
            <h1><?php echo isset($guide_title) ? $guide_title : 'Visual Process Guide'; ?></h1>
            <p><?php echo isset($guide_description) ? $guide_description : 'Step-by-step visual instructions to help you complete this process successfully.'; ?></p>
        </div>

         Progress Bar 
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>

         Step Navigation 
        <div class="step-navigation">
            <div class="step-counter">
                Step <span id="currentStep">1</span> of <span id="totalSteps">5</span>
            </div>
            <div class="nav-buttons">
                <button class="nav-btn" id="prevBtn" onclick="changeStep(-1)" disabled>Previous</button>
                <button class="nav-btn" id="nextBtn" onclick="changeStep(1)">Next</button>
            </div>
        </div>

         Step 1: Getting Started 
        <div class="step-container active" data-step="1">
            <div class="step-title">
                <div class="step-number">1</div>
                Getting Started
            </div>
            <div class="step-description">
                Welcome to this auditing guide. Before we begin, there are two ways to start an audit: One, asset management can provide an excel sheet to insert. Two, simply click the audit button from the profile page that you can make <a href="https://dataworks-7b7x.onrender.com/asset-manager/asset-ui.php">Here</a>.
            </div>
            
            <div class="image-container">
                <img src="path/to/your/screenshot1.png" alt="Getting started screenshot" class="step-image">
                <div class="image-caption">Example: Profile Audit Button Page</div>
            </div>
            <div class="image-container">
                <img src="path/to/your/screenshot1.png" alt="Getting started screenshot" class="step-image">
                <div class="image-caption">Example: Upload Excel Page</div>
            </div>

            <div class="tip-box">
                <strong>💡 Tip:</strong> Make sure the excel is in a recognizable format. For more details on this check out this <a href="https://dataworks-7b7x.onrender.com/faq/audit-excel-format.php">page</a>. Once you start the audit there are 12 hours, starting from your login time, to save the audit.
            </div>

            <div class="action-list">
                <h4>What you'll need:</h4>
                <ol>
                    <li>To be logged in</li>
                    <li>Have a Valid Excel or Asset profile page</li>
                </ol>
            </div>
        </div>

         Step 2: Scanning 
        <div class="step-container" data-step="2">
            <div class="step-title">
                <div class="step-number">2</div>
                Inputting Tags
            </div>
            <div class="step-description">
                At the top right you will see Input fields. The left single one is the room tag number. If it is not available, please put in the room number and it is recommended to put in a ticket for asset management to come and tag that room. 
            </div>
            
            <div class="image-container">
                <img src="path/to/your/screenshot2.png" alt="Navigation screenshot" class="step-image">
                <div class="image-caption">Room and Asset Insert</div>
            </div>

            <div class="tip-box">
                <strong>💡 Tip:</strong> Any asset you enter will be under the room you have inputted on the left. Additionally, the toggle switch can be checked off to stop auto tabbing.
            </div>
            <div class="action-list">
                <h4>Steps to follow:</h4>
                <ol>
                    <li>Input room tag/number</li>
                    <li>Input all tags found in the room</li>
                    <li>Click submit once tags run out</li>
                </ol>
            </div>
        </div>

         Step 3: Notes
        <div class="step-container" data-step="3">
            <div class="step-title">
                <div class="step-number">3</div>
                Notes
            </div>
            <div class="step-description">
                The location to change the room and to input notes can be found on the right of every asset below (Room: left input, Notes: right input). Assets that were not intially part of the process will be shown as blue. Assets that were scanned and part of the process are green. Assets that were not scanned are left as red. 
            </div>
            
            <div class="image-container">
                <img src="path/to/your/screenshot3.png" alt="Main process screenshot" class="step-image">
                <div class="image-caption">Asset Example</div>
            </div>

            <div class="warning-box">
                <strong>⚠️ Important:</strong> Double-check all information before proceeding to avoid errors.
            </div>

            <div class="action-list">
                <h4>Steps:</h4>
                <ol>
                    <li>Edit Notes or Rooms as needed</li>
                </ol>
            </div>
        </div>

         Step 4: Finishing Up 
        <div class="step-container" data-step="4">
            <div class="step-title">
                <div class="step-number">4</div>
                Finishing
            </div>
            <div class="step-description">
                Once all assets that can be found are inserted. Click the 'Save Audit' button located at the top right. It is recommended to also click the 'Export' button located in the middle towards the top. Once you click 'Save Audit' a insert field will appear. This field can be left blank, but it is preferred to write the name of anybody who has helped with the audit.
            </div>
            
            <div class="image-container">
                <img src="path/to/your/screenshot4.png" alt="Verification screenshot" class="step-image">
                <div class="image-caption">Button locations</div>
            </div>

            <div class="highlight-box">
                <strong>✅ Success indicators:</strong> Look for confirmation messages, updated status, or new entries in the system.
            </div>
        </div>

         Step 5: Afterwards
        <div class="step-container" data-step="5">
            <div class="step-title">
                <div class="step-number">5</div>
                Afterwards
            </div>
            <div class="step-description">
                Congratulations! You've successfully completed the process. Here's what happens next.
            </div>
            
            <div class="image-container">
                <img src="path/to/your/screenshot5.png" alt="Completion screenshot" class="step-image">
                <div class="image-caption">Final confirmation or next steps</div>
            </div>

            <div class="completion-section">
                <h3>🎉 Process Complete!</h3>
                <p>You have successfully completed this process. The changes should now be visible in the system. The audit can be seen <a href="https://dataworks-7b7x.onrender.com/audit-history/search-history.php">Here</a></p>
            </div>
        </div>
    </div>

    <script>
        let currentStepNum = 1;
        const totalSteps = 5;

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
