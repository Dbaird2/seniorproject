<?php
include_once("../navbar.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit Process Guide</title>
  <style>
    /* Base styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      line-height: 1.6;
      color: #333;
      background-color: #f5f8ff;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }

    /* Header styles */
    header {
      text-align: center;
      margin-bottom: 3rem;
    }

    header h1 {
      color: #0056b3;
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }

    header p {
      color: #555;
      font-size: 1.2rem;
    }

    /* Step card styles */
    .steps-container {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .step-card {
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 86, 179, 0.1);
    }

    .step-header {
      background-color: #0056b3;
      padding: 1rem 1.5rem;
      color: white;
    }

    .step-header h2 {
      font-size: 1.3rem;
      font-weight: 600;
    }

    .step-content {
      padding: 1.5rem;
    }

    .image-container {
      margin-bottom: 1rem;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      overflow: hidden;
    }

    .image-container img {
      width: 100%;
      height: auto;
      display: block;
    }

    .step-content p {
      color: #444;
      margin-bottom: 1rem;
    }

    /* Note styles */
    .note,
    .info-note {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 6px;
    }

    .note {
      background-color: #fff8e6;
      border-left: 4px solid #ffc107;
    }

    .info-note {
      background-color: #e6f4ff;
      border-left: 4px solid #0056b3;
    }

    .note p,
    .info-note p {
      margin: 0;
      color: #333;
    }

    /* Active step highlight */
    .active-step {
      box-shadow: 0 0 0 2px #0056b3, 0 4px 12px rgba(0, 86, 179, 0.2);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      header h1 {
        font-size: 2rem;
      }

      .step-header h2 {
        font-size: 1.1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Audit Process Guide</h1>
      <p>Follow these steps to complete your audit</p>
    </header>

    <div class="steps-container">
      <!-- Step 1 -->
      <div class="step-card">
        <div class="step-header">
          <h2>Step 1: Click 'Start an Audit'</h2>
        </div>
        <div class="step-content">
          <div class="image-container">
            <img src="https://via.placeholder.com/600x300" alt="Screenshot showing the Start an Audit button">
          </div>
          <p>Begin by clicking the 'Start an Audit' button on the main dashboard to initiate the audit process.</p>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="step-card">
        <div class="step-header">
          <h2>Step 2: Click 'Choose File'</h2>
        </div>
        <div class="step-content">
          <div class="image-container">
            <img src="https://via.placeholder.com/600x300" alt="Screenshot showing the Choose File button">
          </div>
          <p>Select the 'Choose File' button to upload your file for auditing.</p>
          <div class="note">
            <p><strong>Note:</strong> This step requires the file to be in XLS or XLSX format.</p>
          </div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="step-card">
        <div class="step-header">
          <h2>Step 3: Insert Tag Numbers and Submit</h2>
        </div>
        <div class="step-content">
          <div class="image-container">
            <img src="https://via.placeholder.com/600x300" alt="Screenshot showing where to insert tag numbers">
          </div>
          <p>Insert the required tag numbers in the field on the right side of the screen, then click the 'Submit' button to process this entry.</p>
        </div>
      </div>

      <!-- Step 4 -->
      <div class="step-card">
        <div class="step-header">
          <h2>Step 4: Repeat Step 3 Until Complete</h2>
        </div>
        <div class="step-content">
          <p>Continue inserting tag numbers and submitting until you have processed all your scans. The system will keep track of your progress.</p>
        </div>
      </div>

      <!-- Step 5 -->
      <div class="step-card">
        <div class="step-header">
          <h2>Step 5: Export Excel File</h2>
        </div>
        <div class="step-content">
          <p>When you've completed all scans, scroll down to the bottom of the page and click the "Export Excel File" button to download your audited Excel sheet.</p>
          <div class="info-note">
            <p><strong>Note:</strong> The exported file will have "_AUDIT" appended to the end of your original file name.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // This script adds interactivity to the guide
    document.addEventListener("DOMContentLoaded", () => {
      // Add a class to highlight the step when clicked
      const stepCards = document.querySelectorAll(".step-card");

      stepCards.forEach((card) => {
        card.addEventListener("click", function () {
          // Remove active class from all cards
          stepCards.forEach((c) => c.classList.remove("active-step"));

          // Add active class to clicked card
          this.classList.add("active-step");
        });
      });
    });
  </script>
</body>
</html>
