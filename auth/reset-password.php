<?php
// Initialize variables for form fields and error messages
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = $token_err = "";
$reset_success = false;
$token_valid = false;
$token = "";

// Check if token is provided in URL
if (isset($_GET["token"]) && !empty(trim($_GET["token"]))) {
    $token = trim($_GET["token"]);
    
    // In a real application, you would verify the token here
    // For example:
    // $sql = "SELECT email, expiry FROM password_resets WHERE token = ? AND expiry > NOW()";
    // $stmt = mysqli_prepare($link, $sql);
    // mysqli_stmt_bind_param($stmt, "s", $param_token);
    // $param_token = $token;
    // mysqli_stmt_execute($stmt);
    // mysqli_stmt_store_result($stmt);
    
    // if(mysqli_stmt_num_rows($stmt) == 1) {
    //     mysqli_stmt_bind_result($stmt, $email, $expiry);
    //     if(mysqli_stmt_fetch($stmt)) {
    //         $token_valid = true;
    //     }
    // }
    
    // For this example, we'll just assume the token is valid if it's not empty
    // In a real application, you would check against your database
    if (!empty($token)) {
        $token_valid = true;
    } else {
        $token_err = "Invalid password reset token.";
    }
} else {
    $token_err = "No password reset token provided.";
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";     
    } elseif (strlen(trim($_POST["new_password"])) < 8) {
        $new_password_err = "Password must have at least 8 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
        
        // Check password strength
        if (!preg_match("/[A-Z]/", $new_password)) {
            $new_password_err = "Password must contain at least one uppercase letter.";
        } elseif (!preg_match("/[a-z]/", $new_password)) {
            $new_password_err = "Password must contain at least one lowercase letter.";
        } elseif (!preg_match("/[0-9]/", $new_password)) {
            $new_password_err = "Password must contain at least one number.";
        }
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }
    
    // Check input errors before updating the password
    if (empty($new_password_err) && empty($confirm_password_err)) {
        
        // In a real application, you would update the user's password here
        // For example:
        // $sql = "SELECT email FROM password_resets WHERE token = ?";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "s", $param_token);
        // $param_token = $token;
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_bind_result($stmt, $email);
        // mysqli_stmt_fetch($stmt);
        // mysqli_stmt_close($stmt);
        
        // $sql = "UPDATE users SET password = ? WHERE email = ?";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_email);
        // $param_password = password_hash($new_password, PASSWORD_DEFAULT);
        // $param_email = $email;
        // mysqli_stmt_execute($stmt);
        
        // $sql = "DELETE FROM password_resets WHERE token = ?";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "s", $param_token);
        // mysqli_stmt_execute($stmt);
        
        // For this example, we'll just set a success flag
        $reset_success = true;
        
        // Clear form fields after successful submission
        $new_password = $confirm_password = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
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

        /* Password requirements */
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #666;
        }

        .password-requirements ul {
            margin-top: 0.25rem;
            padding-left: 1.5rem;
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
            <h1>Reset Password</h1>
            <p>Create a new secure password</p>
        </header>

        <div class="form-card">
            <div class="form-header">
                <h2>New Password</h2>
            </div>
            <div class="form-content">
                <?php if($reset_success): ?>
                    <div class="alert alert-success">
                        <p>Your password has been reset successfully!</p>
                    </div>
                    <div class="login-link">
                        <a href="login.php" class="btn btn-primary btn-block">Log In with New Password</a>
                    </div>
                <?php elseif(!$token_valid): ?>
                    <div class="alert alert-danger">
                        <p><?php echo $token_err; ?></p>
                    </div>
                    <div class="login-link">
                        <p>The password reset link is invalid or has expired.</p>
                        <a href="forgot-password.php">Request a new password reset link</a>
                    </div>
                <?php else: ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" method="post" id="reset-password-form">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                            <?php if(!empty($new_password_err)): ?>
                                <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                            <?php endif; ?>
                            <div class="password-requirements">
                                <p>Password must:</p>
                                <ul>
                                    <li>Be at least 8 characters long</li>
                                    <li>Include at least one uppercase letter</li>
                                    <li>Include at least one lowercase letter</li>
                                    <li>Include at least one number</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                            <?php if(!empty($confirm_password_err)): ?>
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("reset-password-form");
            
            if (form) {
                form.addEventListener("submit", function(event) {
                    let isValid = true;
                    
                    // Validate new password
                    const newPassword = document.getElementById("new_password");
                    if (newPassword.value === "") {
                        showError(newPassword, "Please enter a new password.");
                        isValid = false;
                    } else if (newPassword.value.length < 8) {
                        showError(newPassword, "Password must have at least 8 characters.");
                        isValid = false;
                    } else if (!/[A-Z]/.test(newPassword.value)) {
                        showError(newPassword, "Password must contain at least one uppercase letter.");
                        isValid = false;
                    } else if (!/[a-z]/.test(newPassword.value)) {
                        showError(newPassword, "Password must contain at least one lowercase letter.");
                        isValid = false;
                    } else if (!/[0-9]/.test(newPassword.value)) {
                        showError(newPassword, "Password must contain at least one number.");
                        isValid = false;
                    } else {
                        clearError(newPassword);
                    }
                    
                    // Validate confirm password
                    const confirmPassword = document.getElementById("confirm_password");
                    if (confirmPassword.value === "") {
                        showError(confirmPassword, "Please confirm the password.");
                        isValid = false;
                    } else if (newPassword.value !== confirmPassword.value) {
                        showError(confirmPassword, "Passwords did not match.");
                        isValid = false;
                    } else {
                        clearError(confirmPassword);
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
        });
    </script>
</body>
</html>
