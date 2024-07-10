<?php
include '../GeneralBackEnd/ServerChecks.php';
session_start(); // Start the session at the beginning of the script
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user inputs from POST request and escape them
    $email = htmlspecialchars($_POST['email']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
   
    if ($email == "") {
        $error = "No email provided.";
        echo $error;
        exit;
    }

    if ($username == "") {
        $error = "No username provided.";
        echo $error;
        exit;
    }

    if ($password == "" || $confirmPassword == "") {
        $error = "Password cannot be empty.";
        echo $error;
        exit;
    } 

    // Check password restrictions
    if (strlen($password) < 6 || strlen($password) > 12 || !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $error = "Password must be between 6 and 12 characters long and contain at least one special character (!@#$%^&*(),.?\":{}|<>)";
        echo $error;
        exit;
        // Don't process further if password restrictions are not met
    }

    if($confirmPassword != $password){
        $error = "Passwords do not match.";
        echo $error;
        exit;
    }

    // Connect to the database
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ---------------
    // Prepare the SQL statement with a placeholder for the email
    $checkEmailSql = "SELECT * FROM accounts WHERE email = ?";
    $stmt = $conn->prepare($checkEmailSql);

    // Bind the email parameter to the prepared statement
    $stmt->bind_param("s", $email);

    // Execute the prepared statement
    $stmt->execute();

    // Get the result set
    $emailResult = $stmt->get_result();

    if ($emailResult->num_rows > 0) {
        $error = "Email is already in use";
        echo $error;
        exit;
    }

    $checkUsernameSql = "SELECT * FROM accounts WHERE username = ?";
    $stmt = $conn->prepare($checkUsernameSql);

    // Bind the username parameter to the prepared statement
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $usernameResult = $stmt->get_result();

    if ($usernameResult->num_rows > 0) {
        $error = "Username is already in use";
        echo $error;
        exit;
    }

    // Generate random salt
    $salt = bin2hex(random_bytes(16)); // Generate 16 bytes of random data

    // Concatenate salt and password
    $saltedPassword = $salt . $password;

    // Hash the salted password
    $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

    //-----------
    $sql = "INSERT INTO accounts (email, username, password, salt) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters to the prepared statement
    $stmt->bind_param("ssss", $email, $username, $hashedPassword, $salt);
    //-----------
    // Prepare SQL statement to insert user data
    $sql = "INSERT INTO accounts (email, username, password, salt) VALUES ('$email','$username','$hashedPassword','$salt')";

    // Execute statement
    if ($stmt->execute()) {
        // Generate unique cookie value
        $cookieValue = uniqid();

        // Calculate expiration time (24 hours from now)
        $expirationTime = time() + (30 * 24 * 3600); // 24 hours * 3600 seconds per hour

        // Set the cookie with the calculated expiration time
        setcookie("username", $cookieValue, [
            'expires' => $expirationTime,
            'path' => '/',
            'secure' => true,
            'samesite' => 'None'
        ]);
        

        // Insert cookie value and expiration time into the database
        $cookieExpirationDateTime = date('Y-m-d H:i:s', $expirationTime);
        $insertCookieSql = "UPDATE accounts SET cookie='$cookieValue', expiration_time='$cookieExpirationDateTime' WHERE username='$username'";
        $conn->query($insertCookieSql);
        $error = "";
        echo $error;
        exit;

     
    } else {
        $error = "" . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function togglePopup(alert) { 
            document.getElementById("error").innerHTML = alert;
            const overlay = document.getElementById('popupOverlay'); 
            overlay.classList.toggle('show'); 
        } 

        $(document).ready(function(){
            $("#registrationForm").submit(function(event){
                event.preventDefault(); // Prevent default form submission
                var formData = $(this).serialize(); // Serialize form data
                $.ajax({
                    url: "Register.php", // URL to send the AJAX request
                    type: "POST",
                    data: formData,
                    success: function(response){
                        if (response.trim() === '') {
                            // If response is empty, redirect the user
                            window.location.href = '../Home/Home.php';
                        } else {
                            // Display the response message in an alert
                            togglePopup(response);
                            //alert(response);
                        }
                    },
                    error: function(xhr, status, error){
                        console.error(error); // Log any errors to the console
                    }
                });
            });
        });
        $(document).ready(function() {
            $("#password").on('keyup', ValidatePassword)
        });
    </script>
</head>
<body>
    <div id="parent">
        <div id="left">
            <!-- fridge image is temporary -->
            <img id="picture" src="FrontEnd/imgs/2ChefsTalking.png">
        </div>
        <div id="right">
            <div id="rightTopContainer">
                <form id="registrationForm" action="" method="post">
                    <div class="titleContainter"><h1 id="title">TELE-FRIDGE</h1></div>
                    <div class="inputContainer"><input class="inputs" id="email" name="email" type="email" placeholder="Email..."/></div>
                    <div class="inputContainer"><input class="inputs" id="username" name="username" type="text" placeholder="Username..."/></div>
                    <div class="inputContainer"><input class="inputs" id="password" name="password" type="password" placeholder="Password..."/></div>
                    <div class="inputContainer"><input class="inputs" id="confirmPassword" name="confirmPassword" type="password" placeholder="Confirm Password..."/></div>
                    <div class="button" id="buttonContainer" style="position: relative;">

                        <img id="registerButton" src="FrontEnd/imgs/register.png" alt="Register" style="position: relative;">
                        <button type="submit" id="submitButton" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 2;"></button>
                   
                    </div>
                </form>
            </div>
            <div id="rightBottomContainer"> 
            <div class="message" id="messageContainer">Already have an account?<div></div><a id="loginLink" href="../Login/Login.php">LOGIN</a></div>            </div>
            <div class="error"><h1 id="passErrorLength" class="passErrorLength nulldisplay">Your password must be between 6 and 12 characters long</h1></div>
            <div class="error"><h1 id="passErrorSpecial" class="passErrorSpecial nulldisplay">Your password must contain a special character</h1></div>
            <div class="error"><h1 id="passErrorMatch" class="passErrorMatch nulldisplay">Your passwords do not match</h1></div>
            <div class="hidden"><input type="hidden" id="defCheck" value=""></input></div>
        </div>
        <script src="FrontEnd/App.js">

        </script>
        <!-- Popup for ERRORS -->
        <div id="popupOverlay" class="overlay-container"> 
            <div class="popup-box"> 
                <h3 style="color: #FBAE3B;">WARNING</h3> 

                <p id="error"> Error </p>
                <br>

                <button class="btn-close-popup" onclick="togglePopup();"> 
                    Close
                </button>
            </div> 
        </div> 





    </div>
</body>
</html>
