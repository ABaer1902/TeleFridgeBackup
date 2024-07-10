
<?php
include '../GeneralBackEnd/ServerChecks.php';
session_start(); // Start the session at the beginning of the script

function processToken() {
    //process token

    if (!isset($_REQUEST["token"])) {
        echo "No Token Found";
        exit;
    }

    $tokenVar = $_GET["token"];
    // //$GLOBALS["token"] = $tokenVar;
    // // Establish connection
    // $serverName = "oceanus.cse.buffalo.edu:3306";
    // $database = "cse442_2024_spring_team_ai_db";
    // $uid = "saj24";
    // $pass = "50407988";

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

    // Check token
    $checkToken = "SELECT * FROM accounts WHERE token='$tokenVar'";
    $tokenResult = $conn->query($checkToken);
    if ($tokenResult->num_rows < 1) {
        echo "Invalid Token";
        exit;
    }
    if ($tokenResult->num_rows == 1) {
        //check token
        $token_query = "SELECT token_exp FROM accounts WHERE token='$tokenVar'";
        $token_result = $conn->query($token_query, $result_mode= MYSQLI_STORE_RESULT);
        $token_result = $token_result->fetch_all($mode = MYSQLI_NUM);
        $token_exp = $token_result[0][0];
        if (strtotime($token_exp) < strtotime("now")) {
            echo "Expired Token";
            exit;
        }
    } else {
        echo "Error, More Than One Account Associated With Token Found. Please resend recovery email.";
        exit;
    }

    $conn->close();
    return $tokenVar;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = htmlspecialchars($_POST['token']);
    // Retrieve user inputs from POST request and escape them
    $password = htmlspecialchars($_POST['password']);
    $confirmPassword = htmlspecialchars($_POST['confirmPassword']);

    if ($password == "" || $confirmPassword == "") {
        echo "Password cannot be empty.";
        exit;
    }

    // Check password restrictions
    if (strlen($password) < 6 || strlen($password) > 12 || !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        echo "Password must be between 6 and 12 characters long and contain at least one special character (!@#$%^&*(),.?\":{}|<>)";
        exit; // Don't process further if password restrictions are not met
    }

    if ($password != $confirmPassword) {
        echo "Passwords Do Not Match";
        exit;
    }

    // Establish connection
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // identify line with token
    $tokenQuery = "SELECT * FROM accounts WHERE token='$token'";
    $tokenResult = $conn->query($tokenQuery);

    // Generate random salt
    $salt = bin2hex(random_bytes(16)); // Generate 16 bytes of random data

    // Concatenate salt and password
    $saltedPassword = $salt . $password;

    // Hash the salted password
    $hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

    // Find line with email and replace password/salt with new values
    $sql = "UPDATE accounts SET password='$hashedPassword', salt='$salt' WHERE token='$token'";

    // Execute statement
    if ($conn->query($sql) === TRUE) {
        //remove token by setting expire time
        $time = strtotime("now");
        $sql = "UPDATE accounts SET token='', token_exp='$time' WHERE token='$token'";
        if ($conn->query($sql))
            echo "Password Successfully Changed. You will now be redirected to Login.";
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        exit;
    }

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
                $("#passwordChange").submit(function(event){
                    event.preventDefault(); // Prevent default form submission
                    var formData = $(this).serialize(); // Serialize form data
                    $.ajax({
                        url: "Recover.php", // URL to send the AJAX request
                        type: "POST",
                        data: formData,
                        success: function(response){
                            if(response.startsWith("Error")) {
                                console.error(response); // Log any errors to the console
                            } else {
                                if (response.includes("Password Successfully Changed")) {
                                    //alert(response);
                                    togglePopupS();
                                } else {
                                    //alert(response);
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
            //$token = processToken();
        ?>

        <div class="parent">
            <div class= "left">
                <img id="image" src="FrontEnd/imgs/2ChefsTalking.png">
            </div>

            <div class="right">
                <div class ="rightTopContainer">
                    <div class="titleContainer"><h1 id="title">TELE-FRIDGE</h1></div>
                    <div class="descriptionContainer"><p class="description">Please enter a new password.</p></div>
                    <form id="passwordChange" action="" method="post">
                        <div class="hiddenContainer"><input name="token", type="hidden", value="<?php echo $token;?>"/></div>
                        <div class="inputContainer"><input class="inputs" id="password" name="password" type="password" placeholder="Password..."/></div>
                        <div class="inputContainer"><input class="inputs" id="confirmPassword" name="confirmPassword" type="password" placeholder="Confirm Password..."/></div>
                        <br>
                        <div class="button" id="buttonContainer" style="position: relative;">
                            <img id="submitBtn" src="FrontEnd/imgs/Submit.png" alt="Submit" style="position: relative; z-index: 1;">
                            <button type="submit" id="submitButton" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; z-index: 2;"></button>
                    </div>
                    </form>
                    
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

                    <p id="success">Password changed successfully!</p>
                    <br>

                    <button class="btn-close-popup" onclick="window.location.href = '../Login/Login.php';"> 
                        Close
                    </button>
                </div> 
            </div> 

        </div>
        
    </body>
</html>