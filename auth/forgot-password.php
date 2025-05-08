<?php
// Initialize variables for form fields and error messages
$email = "";
$email_err = "";
$reset_sent = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Check if email exists in database (in a real application)
    if (empty($email_err)) {
        // In a real application, you would:
        // 1. Check if the email exists in your database
        // 2. Generate a unique token
        // 3. Store the token in your database with an expiration time
        // 4. Send an email with a reset link containing the token
        
        // For example:
        // $sql = "SELECT id FROM users WHERE email = ?";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "s", $param_email);
        // $param_email = $email;
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_store_result($stmt);
        
        // if(mysqli_stmt_num_rows($stmt) == 1) {
        //     $token = bin2hex(random_bytes(32));
        //     $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        //     
        //     $sql = "INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)";
        //     $stmt = mysqli_prepare($link, $sql);
        //     mysqli_stmt_bind_param($stmt, "sss", $param_email, $token, $expiry);
        //     mysqli_stmt_execute($stmt);
        //     
        //     // Send email with reset link
        //     $reset_link = "https://yourwebsite.com/reset-password.php?token=" . $token;
        //     $subject = "Password Reset Request";
        //     $message = "Please click the following link to reset your password: " . $reset_link;
        //     mail($email, $subject, $message);
        // }
        
        // For this example, we'll just set a success flag
        // In a real application, you would always show success even if the email doesn't exist
        // This prevents user enumeration attacks
        $reset_sent = true;
        
        // Clear form field after successful submission
        $email = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        /* Base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f8ff;
        }

        .container {
            max-width: 450px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Header styles */
        header {
            text-align: center;
            margin-bottom: 2rem;
        }

        header h1 {
            color: #0056b3;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        header p {
            color: #555;
            font-size: 1.1rem;
        }

        /* Form styles */
        .form-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.1);
        }

        .form-header {
            background-color: #0056b3;
            padding: 1rem 1.5rem;
            color: white;
        }

        .form-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .form-content {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #444;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn {
            display: inline-block;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-primary:hover {
            background-color: #004494;
            border-color: #004494;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        /* Alert styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        /* Links */
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }

        .login-link a {
            color: #0056b3;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Instructions */
        .instructions {
            margin-bottom: 1.5rem;
            color: #555;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }

            header h1 {
                font-size: 2rem;
            }

            .form-header h2 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Forgot Password</h1>
            <p>Reset your password in two simple steps</p>
        </header>

        <div class="form-card">
            <div class="form-header">
                <h2>Password Recovery</h2>
            </div>
            <div class="form-content">
                <?php if($reset_sent): ?>
                    <div class="alert alert-success">
                        <p>If an account exists with the email you provided, we've sent password reset instructions to that email address.</p>
                    </div>
                    <div class="login-link">
                        <a href="login.php">Return to Login</a>
                    </div>
                <?php else: ?>
                    <div class="instructions">
                        <p>Enter your email address below and we'll send you instructions to reset your password.</p>
                    </div>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="forgot-password-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                            <?php if(!empty($email_err)): ?>
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                        </div>
                    </form>

                    <div class="login-link">
                        Remembered your password? <a href="login.php">Log in</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("forgot-password-form");
            
            if (form) {
                form.addEventListener("submit", function(event) {
                    let isValid = true;
                    
                    // Validate email
                    const email = document.getElementById("email");
                    if (email.value.trim() === "") {
                        showError(email, "Please enter your email.");
                        isValid = false;
                    } else if (!isValidEmail(email.value.trim())) {
                        showError(email, "Please enter a valid email address.");
                        isValid = false;
                    } else {
                        clearError(email);
                    }
                    
                    if (!isValid) {
                        event.preventDefault();
                    }
                });
            }
            
            // Helper functions
            function showError(input, message) {
                input.classList.add("is-invalid");
                
                // Check if error message already exists
                let errorDiv = input.nextElementSibling;
                if (!errorDiv || !errorDiv.classList.contains("invalid-feedback")) {
                    errorDiv = document.createElement("div");
                    errorDiv.className = "invalid-feedback";
                    input.parentNode.insertBefore(errorDiv, input.nextSibling);
                }
                
                errorDiv.textContent = message;
            }
            
            function clearError(input) {
                input.classList.remove("is-invalid");
                
                // Remove error message if it exists
                const errorDiv = input.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains("invalid-feedback")) {
                    errorDiv.textContent = "";
                }
            }
            
            function isValidEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
        });
    </script>
</body>
</html>
