<?php
include '../GeneralBackEnd/ServerChecks.php';

session_start(); // Start the session

// Initialize variables to store the entered username and error message
$enteredUsername = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get input from request and escape them
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Store the entered username
    $enteredUsername = $username;

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

    $checkUserExists = "SELECT * FROM accounts WHERE username=? OR email=?";
    $stmt = $conn->prepare($checkUserExists);

    // Bind parameters
    $stmt->bind_param('ss', $username, $username);

    // Execute the statement
    $stmt->execute();

    // Store the result
    $userResult = $stmt->get_result();

    $method = 'username';

    if ($userResult->num_rows == 0) {
        // No user exists, store error message
        $error = "This user does not exist.";
    } else {
        // get the corresponding row
        $row = $userResult->fetch_assoc();
        $storedSalt = $row['salt'];
        if ($row['email'] == $username) {
            $method = 'email';
        }

        // Concatenate the stored salt with the provided password and hash it
        $saltedPassword = $storedSalt . $password;
        $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

        // Retrieve the stored hashed password
        $storedHashedPassword = $row['password'];

        // Check if the hashed password matches the stored hashed password
        if (password_verify($saltedPassword, $storedHashedPassword)) {
            // Passwords match, login successful

            // Generate unique cookie value
            $cookieValue = uniqid();

            // Calculate expiration time (24 hours from now)
            $expirationTime = time() + (30 * 24 * 3600); // 24 hours * 3600 seconds per hour

            // Set the cookie with the calculated expiration time
            setcookie("username", strval($cookieValue), [
                'expires' => strval($expirationTime),
                'path' => '/',
                'secure' => true,
                'samesite' => 'None'
            ]);
            
            

            // Insert cookie value and expiration time into the database
            $cookieExpirationDateTime = date('Y-m-d H:i:s', $expirationTime);
            $insertCookieSql = "UPDATE accounts SET cookie='$cookieValue', expiration_time='$cookieExpirationDateTime' WHERE username='$username'";
            if ($method == 'email') {
                $insertCookieSql = "UPDATE accounts SET cookie='$cookieValue', expiration_time='$cookieExpirationDateTime' WHERE email='$username'";
            }
            $conn->query($insertCookieSql);

            // Set a session variable to hold the notification message
            $_SESSION['notification'] = "Login successful!";

        } else {
            // Invalid password, store error message
            $error = "Incorrect password.";



        }
    }
    // close connection
    $conn->close();

    // Output error message
    if ($error != "") {
        echo $error;
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <div id="parent">
        <div id="left">
            <img id="picture" src="FrontEnd/imgs/2ChefsTalking.png" alt="Fridge Image">
        </div>
        <div id="right">
            <div id="rightTopContainer">
                <div class="titleContainter">
                    <h1 id="title">TELE-FRIDGE</h1>
                </div>
                <form id="loginForm" action="" method="post">
                    <div id="inputsContainer">
                        <div class="inputContainer">
                            <input class="inputs" id="username" type="text" name="username" placeholder="Email/Username..." value="<?php echo htmlspecialchars($enteredUsername); ?>">
                        </div>
                        <div class="inputContainer">
                            <input class="inputs" id="password" type="password" name="password" placeholder="Password...">
                        </div>
                    </div>
                    <div class="button" id="buttonContainer" style="position: relative;">
                        <img id="loginButton" src="FrontEnd/imgs/login.png" alt="Register" style="position: relative; z-index: 1;">
                        <button type="submit" id="submitButton" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 2;"></button>
                    </div>
                </form>
                <script>
                
  
                    function togglePopup(alert) { 
                        document.getElementById("error").innerHTML = alert;
                        const overlay = document.getElementById('popupOverlay'); 
                        overlay.classList.toggle('show'); 
                    } 
                
                // Intercept form submission and handle the response
                document.getElementById("loginForm").addEventListener("submit", function(event) {
                    event.preventDefault(); // Prevent the form from submitting

                    // Serialize form data
                    var formData = new FormData(this);

                    // Send AJAX request to the PHP script
                    fetch("Login.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.text()) // Get the response as text
                    .then(response => {
                        if (response.trim() === '') {
                            // If response is empty, redirect the user
                            window.location.href = '../Home/Home.php';
                        } else {
                            // Display the response message in an alert
                            togglePopup(response);
                            //alert(response);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
                });
            </script>

                
                <div class="message" id="guestContainer">
                    or continue as<a id="link" href="../Home/Home.php">GUEST</a>
                </div>
                <div class="message" id="forgotPasswordContainer">
                    <a id="link" href="../ForgotPassword/ForgotPassword.php">FORGOT PASSWORD?</a>
                </div>
            </div>
            <div id="rightBottomContainer"> 
                <div class="message" id="messageContainer">Don't have an account?<div></div><a id="link" href="../Register/Register.php">SIGN UP</a></div>
            </div>
            <!-- Popup for ERRORS -->
            <div id="popupOverlay" class="overlay-container"> 
                <div class="popup-box"> 
                    <h1 style="color: #FBAE3B;">WARNING</h1> 

                    <p id="error"> Error </p>
                    <br>

                    <button class="btn-close-popup" onclick="togglePopup();"> 
                        Close
                    </button>
                </div> 
            </div> 
        </div>

        



    </div>


    
</body>

</html>
