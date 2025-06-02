<?php
error_reporting(0);
include_once("../config.php");
if (isset($_SESSION['email'])) {
    header("location: https://dataworks-7b7x.onrender.com/index.php");
}
 $email_err = $pw_err = $err = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'] ?? "";
        $pw = $_POST['pw'] ?? "";
        if (!empty($email) && !empty($pw)) {
            
            $stmt = "SELECT email, pw, id, u_role, dept_id FROM user_table WHERE email = ?";
            $stmt = $dbh->prepare($stmt);
            if (!($stmt->execute([$email]))) {
                $err = "Error getting info" . $stmt->errorInfo()[2];
            } else {
                $user_check = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user_check && password_verify($pw, $user_check['pw'])) {
                    $_SESSION['id'] = $user_check['id'];
                    $_SESSION['email'] = $user_check['email'];
                    $_SESSION['role'] = $user_check['u_role'];
                    $_SESSION['deptid'] = $user_check['dept_id'];

                    $query = "UPDATE user_table SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                    $stmt2 = $dbh->prepare($query);
                    if ($stmt2->execute([$_SESSION['id']])) {
                        header("location: https://dataworks-7b7x.onrender.com/index.php");
                    } else {
                        error_log("Error updating last_login" . $stmt->errorInfo());
                    }
                }
            }
            $stmt = NULL;
            $stmt2 = NULL;
        } else {
            $err = "Invalid email or password";
        }

    }
include_once("../navbar.php");
 ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
  font-family: "Lato", sans-serif;
            display: flex;
            flex-direction: column; 
            justify-content: space-between; 
            height: 100vh; 
            margin: 0; 
        }
        .container {
            width:30rem;
            display: flex;
            flex-direction: column; 
            margin: auto;
            border: 1px solid black;
            border-radius: 15px;    
            box-shadow: 2px 2px 2px #003594;

        }
        #form-label {
            margin-bottom:0.5em;
            margin-left:0.5rem;
            color: black;
            font-weight:bold;
            font-size: max(1.5vh, 0.8rem);        }
        .header {
            background-color: #003594;
            color: #FFC72C;
            text-align: center;
            border-top-left-radius: 15px;    
            border-top-right-radius: 15px;    
        }
        .formAtt {
            margin-bottom:1rem;
            width:100%;
            color: rgb(36, 35, 42);
            font-size: calc(1vh + 0.3vw);
            line-height: 20px;
            min-height: 5vh;
            border-radius: 4px;
            padding: 8px 16px;
            border: 2px solid transparent;
            box-shadow: rgb(0 0 0 / 12%) 0px 1px 3px, rgb(0 0 0 / 24%) 0px 1px 2px;
            background: rgb(251, 251, 251);
            transition: all 0.1s ease 0s;
            :focus{
                border: 2px solid #003594;
            }
        }
        .footer {
            text-align: center;
            font-size: max(1.5vh, 0.8rem);
        }
       .button-58 {
            width: 50%;
            align-items: center;
            background-color: #003594;
            border: 2px solid #06f;
            box-sizing: border-box;
            color: #FFC72C;
            cursor: pointer;
            display: inline-flex;
            fill: #000;
            font-size: max(1.5vh, 0.8rem);
            font-weight: 600;
            height: calc(3vh + 1.2vw);
            justify-content: center;
            letter-spacing: -.8px;
            line-height: 24px;
            min-width: 140px;
            outline: 0;
            padding: 0 17px;
            text-align: center;
            text-decoration: none;
            transition: all .3s;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            border-radius: 30px;
            -ms-transform: translate(50%, 50%);
            transform: translate(50%, 50%);
        }

        .button-58:focus {
            color: #171e29;
        }

        .button-58:hover {
            background-color: #3385ff;
            border-color: #3385ff;
            fill: #06f;
        }

        .button-58:active {
            background-color: #3385ff;
            border-color: #3385ff;
            fill: #06f;
        }

        @media (min-width: 768px) {
            .button-58 {
                min-width: 170px;
            }
        }
        .forgot-pass {
            text-align:right;
            display:block;
            margin-right:1.5rem;
            text-decoration: none;
            
        }
        a:hover {
            text-decoration: underline;
        }
        a:visited  {
            text-decoration: none;
        }
        
     
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size:calc(2vh + 1.1vw);">Welcome Back</h2>
    </div>
            <div class="body">
                <form id="login-form" method="post" action="login.php" oninput="validateForm()" onsubmit="return validateForm()">
                    <label id="form-label" for="email">Email Address</label>
                    <input class="formAtt" type="text" name="email" id="email" placeholder="example@csub.edu" onblur="validateEmail()" required>
                    <div id="err_email" style="color:red;"><?php $err?></div>

                    <br>
                    <label id="form-label" for="pw">Password</label>
                    <input class="formAtt" type="password" name="pw" id="pw" placeholder="Enter Password" required>
                    <?php echo "<label style='color:red; text-align: center;'> $err </label>"; ?>
                    <br>
                    <a class="forgot-pass" href="forgot-password.php" >Forgot password?</a>
              

                    <button id="btn" class="button-58" role="button">Log in</button>
                </form>
                <div class="footer">
                    <br>
                    <p></p> 
                </div>
        </div>
    </div>
    <script>
        function validateEmail () {
            var email = document.getElementById("email").value;
            var err_email = document.getElementById("err_email");

            const email_check = email.slice(-9);

            if (email_check !== '@csub.edu') {
                err_email.textContent = "CSUB email only";
                return false;
            }

            err_email.textContent = "";
            return true;
        }

        function validateForm() {

            var isEmailValid = validateEmail();

            var btn = document.getElementById("btn");

            if (!isEmailValid) {

                btn.style.display="none";
                return false
            }
            btn.style.display="inline-flex";
            return true;
        }
    </script>                                                                                                       
</body>
</html>
