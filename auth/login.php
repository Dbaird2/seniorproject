<?php
// Initialize variables for form fields and error messages
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Check if remember me was checked
    $remember = isset($_POST["remember"]) ? true : false;
    
    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // In a real application, you would verify the user credentials here
        // For example:
        // $sql = "SELECT id, email, password FROM users WHERE email = ?";
        // $stmt = mysqli_prepare($link, $sql);
        // mysqli_stmt_bind_param($stmt, "s", $param_email);
        // $param_email = $email;
        // mysqli_stmt_execute($stmt);
        // mysqli_stmt_store_result($stmt);
        
        // if(mysqli_stmt_num_rows($stmt) == 1) {
        //     mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password);
        //     if(mysqli_stmt_fetch($stmt)) {
        //         if(password_verify($password, $hashed_password)) {
        //             session_start();
        //             $_SESSION["loggedin"] = true;
        //             $_SESSION["id"] = $id;
        //             $_SESSION["email"] = $email;
        //             
        //             if($remember) {
        //                 // Set cookies for remember me functionality
        //                 $token = bin2hex(random_bytes(16));
        //                 // Store token in database associated with user
        //                 setcookie("remember_me", $token, time() + 86400 * 30, "/");
        //             }
        //             
        //             header("location: https://datawork-7b7x.onrender.com/index.php");
        //         } else {
        //             $login_err = "Invalid email or password.";
        //         }
        //     }
        // } else {
        //     $login_err = "Invalid email or password.";
        // }
        
        // For this example, we'll just set a login error
        // In a real application, you would verify against your database
        if ($email === "user@example.com" && $password === "password123") {
            // Redirect to dashboard or home page
            // header("location: dashboard.php");
            // exit;
            
            // For demo purposes, we'll just clear the form
            $email = $password = "";
            // And set a success message
            $success = true;
        } else {
            $login_err = "Invalid email or password.";
        }
    }
}
include_once("navbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

        /* Checkbox styling */
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .form-check-input {
            margin-right: 0.5rem;
            width: 18px;
            height: 18px;
        }

        .form-check-label {
            font-weight: normal;
        }

        /* Alert styles */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        /* Links */
        .forgot-password {
            text-align: right;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: #0056b3;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .signup-link {
            text-align: center;
            margin-top: 1rem;
        }

        .signup-link a {
            color: #0056b3;
            text-decoration: none;
        }

        .signup-link a:hover {
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
            <h1>Welcome Back</h1>
            <p>Log in to access your account</p>
        </header>

        <div class="form-card">
            <div class="form-header">
                <h2>Log In</h2>
            </div>
            <div class="form-content">
                <?php if(!empty($login_err)): ?>
                    <div class="alert alert-danger"><?php echo $login_err; ?></div>
                <?php endif; ?>
                
                <?php if(isset($success) && $success): ?>
                    <div class="alert alert-success">You have been successfully logged in!</div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <?php if(!empty($email_err)): ?>
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <?php if(!empty($password_err)): ?>
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="forgot-password">
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Log In</button>
                    </div>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side form validation
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("login-form");
            
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
                
                // Validate password
                const password = document.getElementById("password");
                if (password.value === "") {
                    showError(password, "Please enter your password.");
                    isValid = false;
                } else {
                    clearError(password);
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
