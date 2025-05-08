<?php
// Initialize variables for form fields and error messages
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
        // Here you would typically check if email already exists in database
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        // In a real application, you would insert the user into your database here
        // For example:
        // $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_email, $param_password);
        // $param_name = $name;
        // $param_email = $email;
        // $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
        // mysqli_stmt_execute($stmt);
        
        // For this example, we'll just set a success flag
        $registration_success = true;
        
        // Clear form fields after successful submission
        $name = $email = $password = $confirm_password = "";
    }
}
include_once("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
            max-width: 500px;
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

        /* Success message */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        /* Login link */
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
            <h1>Create Account</h1>
            <p>Sign up to get started with our service</p>
        </header>

        <?php if($registration_success): ?>
            <div class="alert alert-success">
                Your account has been created successfully! You can now <a href="login.php">log in</a>.
            </div>
        <?php endif; ?>

        <div class="form-card">
            <div class="form-header">
                <h2>Sign Up</h2>
            </div>
            <div class="form-content">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="signup-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <?php if(!empty($name_err)): ?>
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <?php if(!empty($email_err)): ?>
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                        <?php if(!empty($password_err)): ?>
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                        <?php if(!empty($confirm_password_err)): ?>
                            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </div>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Log in</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("signup-form");
            
            form.addEventListener("submit", function(event) {
                let isValid = true;
                
                // Validate name
                const name = document.getElementById("name");
                if (name.value.trim() === "") {
                    showError(name, "Please enter your name.");
                    isValid = false;
                } else {
                    clearError(name);
                }
                
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
                
                // Validate password
                const password = document.getElementById("password");
                if (password.value === "") {
                    showError(password, "Please enter a password.");
                    isValid = false;
                } else if (password.value.length < 6) {
                    showError(password, "Password must have at least 6 characters.");
                    isValid = false;
                } else {
                    clearError(password);
                }
                
                // Validate confirm password
                const confirmPassword = document.getElementById("confirm_password");
                if (confirmPassword.value === "") {
                    showError(confirmPassword, "Please confirm password.");
                    isValid = false;
                } else if (password.value !== confirmPassword.value) {
                    showError(confirmPassword, "Passwords did not match.");
                    isValid = false;
                } else {
                    clearError(confirmPassword);
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
            
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
