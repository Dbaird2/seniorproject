<?php
require_once("../config.php");

ini_set('display_error', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$err = 0;
$user_err = $email_err = $f_name_err = $l_name_err = '';
$pw_err = $cpw_err = $dept_err = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_err = (($username = $_POST['username'] ?? '') != '') ? "": "Empty Username field";
    $email_err = (($email = $_POST['email'] ?? '') != '') ? "" : "Empty Email Field";
    $f_name = $_POST['f_name'] ?? '';
    $l_name = $_POST['l_name'] ?? '';
    $password = $_POST['pw'] ?? '';
    $con_password = $_POST['cpw'] ?? '';
    $deptid = $_POST['deptid'] ?? '';
    $dept_id_array = array_values(array_filter(explode(',', $deptid), fn($v) => trim($v) !== ''));
    $role = $_POST['role'] ?? ''; 

    if (!empty($username)) {
         
            $stmt = "SELECT * FROM user_table WHERE username = ? OR email = ?;";
            $stmt = $dbh->prepare($stmt);
            if (!($stmt->execute([$username, $email]))) {
                echo "<p> Error with database </p>";
                $err = 1;
            } else {
                $user_check = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user_check > 0) {
                    if ($user_check['username'] === $username) {
                        $user_err = "Username or Email already exists";
                        $err = 1;
                    } 
                    if ($user_check['email'] === $email) {
                        $user_err = "Username or Email already exists";
                        $err = 1;
                    } 
                } 
            }
                
    } 


    if (!empty($con_password)) {
        if ($con_password === $password) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        } else {
            $cpw_err = "Passwords must match.";
            $err = 1;
        }
    }
    if (!empty($deptid)) {
        $placeholder = implode(',', array_fill(0, count($dept_id_array), '?'));
        $stmt = "SELECT distinct dept_id FROM department where dept_id IN ($placeholder)";
        $stmt = $dbh->prepare($stmt);
        $stmt->execute($dept_id_array);
        if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
            $dept_err = "Department does not exist";
            $err = 1;
        }

    }
    if (!$err) {
        
        $stmt = "INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id) 
        VALUES (:username, :pw, :email, :u_role, :f_name, :l_name, :dept::VARCHAR[]);";
        $stmt = $dbh->prepare($stmt);
        $dept_cust = "UPDATE department SET custodian = ? WHERE dept_id IN ($placeholder)";
        $dept_stmt = $dbh->prepare($dept_cust);
        $full_name = $f_name . " " . $l_name;
        try {
            if ($stmt->execute([':username'=>$username, ':pw'=>$password, 
                ':email'=>$email, ':u_role'=>$role, ':f_name'=>$f_name, ':l_name'=>$l_name,
                ':dept'=>$dept_id_array])) {
                $dept_stmt->execute([$full_name], $dept_id_array);
                header("Location: https://dataworks-7b7x.onrender.com/index.php");
            }
        } catch (PDOException $e) {
            echo '<pre>';
            var_dump($dept_id_array);
            echo '</pre>';
            error_log($e->getMessage());
        }
        
    }

}
include_once("../navbar.php");

?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
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
            position: relative;
            top: 40px;
            width:24vw;
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
            font-size: max(1.5vh, 0.8rem);
        }
        .header {
            font-size: max(1.5vh, 0.8rem);
            background-color: #003594;
            color: #FFC72C;
            text-align: center;
            border-top-left-radius: 15px;    
            border-top-right-radius: 15px;    
        }
        .formAtt {
            margin-bottom:1vh;
            width: 100%;
            height: 1.3vh;
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
            display: inline-block;
            fill: #000;
            font-size: calc(0.6vh + 0.4vw);
            font-weight: 600;
            height: calc(4vh + 1.2vw);
            justify-content: center;
            letter-spacing: -.8px;
            line-height: 24px;
            min-width:  2vw;
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
            transform: translate(5.7vw, 1vh);
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

        @media (min-width: 2vw) {
            .button-58 {
                min-width: 1vw;
            }
        }

        .select {
            width: 100%;
            min-width: 15ch;
            max-width: 30ch;
            border: 1px solid var(--select-border);
            border-radius: 0.25em;
            padding: 0.25em 0.5em;
            font-size: max(1.5vh, 0.8rem);
            cursor: pointer;
            line-height: 1.1;
            background-color: #fff;
            background-image: linear-gradient(to top, #f9f9f9, #fff 33%);
              grid-template-areas: "select";

        }   
     
        select{
            font-size: 0.8rem;
            appearance: base-select;
            color: #71717a;
            background-color: transparent;
            width: 180px;
            box-sizing: border-box;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e4e4e7;
            border-radius: calc(0.5rem - 2px);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }     
        select > button {
            display: flex;
            width: 100%;
            color: currentColor;
        }  
        select::picker(select) {
            font-size: 1rem;
            appearance: base-select;
            border: 1px solid #e4e4e7;
            padding: 0.25rem;
            margin-top: 0.25rem;
            border-radius: calc(0.5rem - 2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -2px rgba(0, 0, 0, 0.1);
            cursor: default;
            transition: opacity 225ms ease-in-out, transform 225ms ease-in-out;
            transform-origin: top;
            transform: translateY(0);
            opacity: 1;

            @starting-style {
            transform: translateY(-0.25rem) scale(0.95);
            opacity: 0;
            }
        }
        select:focus-visible {
            outline: 2px solid #a1a1aa;
            outline-offset: -1px;
        }

        select:has(option:not([hidden]):checked) {
            color: #18181b;
        }
        select option::after {
            content: "";
            width: 1rem;
            height: 1rem;
            margin-left: auto;
            opacity: 0;
            background: center / contain no-repeat
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2318181b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6 9 17l-5-5'%3E%3C/path%3E%3C/svg%3E");
        }


    </style>
    <script>
        function validateEmail () {
            var email = document.getElementById("email").value;
            var err_email = document.getElementById("err_email");

            const email_check = email.slice(-9);

            if (email === "") {
                err_email.textContent = "";
                return false;
            }

            if (email_check !== '@csub.edu') {
                err_email.textContent = "CSUB Only Emails";
                return false;
            }

            err_email.textContent = "";
            return true;
        }
        function validateDept() {
            var dept = document.getElementById("deptid").value;
            var err_dept = document.getElementById("dept_err");

            var dept_regex = /^[D]\d{5}$/;

            if (dept === "") {
                err_dept.textContent = "";
                return false;
            }

            if (!dept_regex.test(dept.trim())) {
                err_dept.textContent = "Department must start with D and have 5 numbers";
                return false;
            } 
            err_dept.textContent = "";
            return true;
            
        }
        function validatePassword() {
            var pw = document.getElementById("pw").value;
            var cpw = document.getElementById("cpw").value;
            var error_message = document.getElementById("err_msg");
            var err_pw = document.getElementById("err_pw");

            var passwordRegex = /^(?=.*?[0-9])(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[^0-9A-Za-z]).{8,32}$/;

            if (pw === "" && cpw === "") {
                error_message.textContent = "";
                return false;
            }

            if (!passwordRegex.test(pw)) {
                err_pw.textContent = "Passwords must be at least 8 characters long, contain one capital letter, and one special character";
                return false;
            } else {
                err_pw.textContent = "";
            }

            
            if (pw !== cpw) {
                error_message.textContent = "Passwords do not match";
                return false;
            }

            
            err_pw.textContent = "";
            error_message.textContent = "";
            return true;
        }
        function validateUsername() {
            var username = document.getElementById("username").value;
            var err_user = document.getElementById("err_user");

            const user_regex = /^[0-9A-Za-z]{4,16}$/;

            if (!user_regex.test(username)) {
                err_user.textContent = "Username must be at least 4 characters"
                return false;
            }
            err_user.textContent = "";
            return true;

        }
        function validateForm() {
            var isDeptValid = validateDept();
            var isEmailValid = validateEmail();
            var isPasswordValid = validatePassword();
            var isUsernameValid = validateUsername();
            var btn = document.getElementById("btn");

            if (!isDeptValid || !isEmailValid || !isPasswordValid || !isUsernameValid) {

                btn.style.display="none";
                return false
            }
            btn.style.display="inline-flex";
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="font-size:calc(2vh + 1vw);">Create an Account</h2>
            <p style="font-size:calc(0.8vh + 0.3vw);"> Please fill in the form to create an account</p>
        </div>
            <div class="body">
                <form id="singup-form" method="post" action="signup.php" oninput="validateForm()" onblur="return validateForm()" >
                    <label id="form-label" for="username">Username</label>
                    <input class="formAtt" type="text" name="username" id="username" placeholder="Choose a username" onblur="validateUsername()" required>
                    <?php echo "<label style='color:red; text-align: center;'>". $user_err ."</label>"; ?>
                    <div id="err_user" style="color:red;font-size:1.1vh;"></div>

                    <label id="form-label" for="email">Email Address</label>
                    <input class="formAtt" type="text" name="email" id="email" placeholder="example@csub.edu" onblur="validateEmail()" required>
                    <?php echo "<label style='color:red; text-align: center;'>". $user_err ."</label>"; ?>
                    <div id="err_email" style="color:red;"></div>


                    <label id="form-label" for="f_name">First Name</label>
                    <input class="formAtt" type="text" name="f_name" id="f_name" placeholder="Jim" required>

                    <label id="form-label" for="l_name">Last Name</label>
                    <input class="formAtt" type="text" name="l_name" id="l_name" placeholder="Bob" required>

                    <label id="form-label" for="pw">Password </label>
                    <input class="formAtt" type="password" name="pw" id="pw" placeholder="Create a password" onblur="validatePassword()" required>
                    <div id="err_pw" style="color:red;"></div>

                    <label id="form-label" for="pw">Confirm Password</label>
                    <input class="formAtt" type="password" name="cpw" id="cpw" placeholder="Confirm your password" onblur="validatePassword()" required>
                    <div id="err_msg" style="color:red;"></div>

                    <label id="form-label" for="deptid">Department ID</label>
                    <input class="formAtt" type="text" name="deptid" id="deptid" placeholder="Ex: D20106" onblur="validateDept()" required>
                    <?php echo "<label style='color:red; text-align: center;'> $dept_err </label>"; ?>
                    <div id="dept_err" style="color:red;"></div>

                    <label id="form-label" for="role">User Role</label>
             
                    <select class="select" name="role" id="role">
                        <option value="user">User</option>
                        <option value="custodian">Custodian</option>
                        <option value="management">Management</option>
                        <option value="admin">Admin</option>
                    </select>

                    <button id="btn" class="button-58" type="submit" role="button" oninput="validateForm()">Create Account</button>
                </form>
                <div class="footer">
                  
                    <p style="padding-top:2vh;font-size:calc(1.0vh +0.5vw);">Already have an account? <a href="login.php">Login Here</a></p> 
                
                    <p style="font-size:calc(1.0vh +0.5vw);">By signing up, you agree to our Terms of Service and Privacy Policy</p>
                </div>
        </div>
    </div>
    <script>
    </script>                                                                                                       
</body>
</html>
