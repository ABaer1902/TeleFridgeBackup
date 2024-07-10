<?php
include '../GeneralBackEnd/ServerChecks.php';
session_start(); // Start the session at the beginning of the script

// Generates a random string of chars to serve as a temporary password
function genTempPassword($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

function sendMail($email, $token, $url) {
    //$url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]/Github/s24semesterproject-overcoded/src/Recover/Recover.php";
    $link = $url . "?token=" . $token;
    $to = $email;
    $subject = "Tele-Fridge Password Reset";
    $message = "Click the link below to reset your password. This link will only be available for 1 hour.\n$link";
    //Tentative, probably generate a few dummy emails to test
    $headers = 'From: nottele-fridge@gmail.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion(); 
    if (mail($to, $subject, $message, $headers)) {
        echo "An email has been sent to $email. ";
        return TRUE;
    } else {
        echo "Error: Mail could not be sent";
        return FALSE;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = htmlspecialchars($_POST['url']);
    // Retrieve user inputs from POST request and escape them
    $email = htmlspecialchars($_POST['email']);

    // Connect to the database
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for associated email
    $checkEmailSql = "SELECT * FROM accounts WHERE email='$email'";
    $emailResult = $conn->query($checkEmailSql);
    if ($emailResult->num_rows < 1) {
        echo "There is no account associated with this email";
        exit;
    }

    //generate token
    $tokenLength = random_int(20,30);
    $token = bin2hex(random_bytes($tokenLength));

    //SQL entry
    $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));; //can modify token lifetime here
    $SQL_entry = "UPDATE accounts SET token = ?, token_exp = ? WHERE email = ?";
    $stmt = $conn->prepare($SQL_entry);
    $stmt->bind_param("sss", $token, $expire, $email);
    if ($stmt->execute()) {
        // Send email
        if (sendMail($email, $token, $url))
            // Return success msg w user's name
            echo "You will now be redirected to login.";
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $stmt->error;
        exit;
    }


    /*
    // Generate a random password to be the temp
    $password = genTempPassword(20);

    // Generate random salt
    $salt = bin2hex(random_bytes(16)); // Generate 16 bytes of random data

    // Concatenate salt and password
    $saltedPassword = $salt . $password;

    // Hash the salted password
    $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

    // Find line with email and replace password/salt with new values
    $sql = "UPDATE accounts SET password='$hashedPassword', salt='$salt' WHERE email='$email'";

    // Execute statement
    if ($conn->query($sql) === TRUE) {
        //Send email
        if (sendMail($email, $token))
            // Return success msg w user's name
            echo "An email has been sent to $email. You will now be redirected to login.";
        exit;
     
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    */

    // Close connection
    $conn->close();
}
?>

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
            
            function togglePopupS() { 
                const overlay = document.getElementById('popupOverlayS'); 
                overlay.classList.toggle('show'); 
            }  
            
            
            $(document).ready(function(){
                $("#recoverEmail").submit(function(event){
                    event.preventDefault(); // Prevent default form submission
                    var formData = $(this).serialize(); // Serialize form data
                    $.ajax({
                        url: "ForgotPassword.php", // URL to send the AJAX request
                        type: "POST",
                        data: formData,
                        success: function(response){
                            if(response.startsWith("Error")) {
                                console.error(response); // Log any errors to the console
                            } else {
                                if (response.startsWith("An email has been sent ")) {
                                    //alert(response);

                                    togglePopupS();
                                    window.location.href = "../Login/Login.php";
                                } else {
                                    //alert(response); // Display response message
                                    togglePopup(response);
                                }
                            }
                        },
                        error: function(xhr, status, error){
                            console.error(error); // Log any errors to the console
                        }
                    });
                });
            });
        </script>
    </head>
    <body>


        <!-- Load React. -->
        <!-- Note: when deploying, replace "development.js" with "production.min.js". -->
        <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
        <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
        <!-- Load our React component. -->
        <script src="FrontEnd/App.js"></script>

        <!-- Access Server Port -->
        <?php
            header('Access-Control-Allow-Origin: http://localhost/');
        ?>



        <!-- BUTTON LIST -->
        <!-- Email Input -->
        <!-- Recover Button -->
        <!-- Go Back Buton -->

        <div class="parent">
            <div class= "left">
                <img id="image" src="FrontEnd/imgs/2ChefsTalking.png">
            </div>
            <div class="right">
                <div class ="rightTopContainer">
                    <div class="titleContainer"><h1 id="title">TELE-FRIDGE</h1></div>
                    <div class="descriptionContainer"><p class="description" id="prompt">Please enter your email.<br> We will send you a recovery email.</p></div>
                    <form id="recoverEmail" action="" method="post">
                        <div class="hiddenContainer"><input name="url", type="hidden", id="url", value=""/></div>
                        <script>document.getElementById('url').value = window.location.href + "/../../Recover/Recover.php";</script>
                        <div class="inputContainer"><input class="inputs" id="email" name="email" type="email" placeholder="Email..."></div>
                        <br>
                    <div class="button" id="buttonContainer" style="position: relative;">
                        <img id="recoverButton" src="FrontEnd/imgs/Recover.png" alt="Recover" style="position: relative; z-index: 1;">
                        <button type="submit" id="submitButton" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 2;"></button>
                    </div>

                    </form>
                    
                </div>
                <div class="rightBottomContainer">
                    <div class="descriptionContainer"><p class="description">Didn't mean to click here?</p><a id="link" href="../Login/Login.php">Go Back</a></div>
                </div>
            </div>

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


            <!-- Popup on SUCCESS -->
            <div id="popupOverlayS" class="overlay-containerS"> 
                <div class="popup-box"> 
                    <h3 style="color: #FBAE3B;">SUCCESS</h3> 

                    <p id="success">Redirecting to the login page...</p>
                    <br>

                    <button class="btn-close-popup" onclick="window.location.href = '../Login/Login.php';"> 
                        Close
                    </button>
                </div> 
            </div> 

        </div>
        
    </body>
</html>