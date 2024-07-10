<?php
// function to check for 'username' cookie
function checkLoggedIn() {
    // if they have a cookie redirect them to home
    if (isset($_COOKIE['username'])) {
        header('Location: ../Home/Home.php');
        exit();
    }
}

// Call the function to check for 'username' cookie
checkLoggedIn();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
</head>

<body>
    <div id="parent">

        <div id="left">
        <div id="leftContainer">
            <!-- Title and Slogan -->
            <div class="titleContainter"><h1 id="title">TELE-FRIDGE</h1></div>
            <div class="message" id="messageContiner">
                <div id="slogan1Container"><p id="slogan1">Whipping Up Recipes from</p></div>
                <div id="slogan2Container"><p id="slogan2">What's on Hand</p></div>
            </div>
        
            <!-- Buttons -->
            <div id="exploreContainer"><a href="../Home/Home.php"><img class="button" id="exploreButton" src="FrontEnd/imgs/explore.png" alt="Explore"></a></div>
            <div id="loginContainer"><a href="../Login/Login.php"><img class="button" id="loginButton" src="FrontEnd/imgs/login.png" alt="Explore"></a></div>

        </div>
        </div>

        <div id="right">
                
            <div id="imageContainer"><img id ="image" src="FrontEnd\imgs\2ChefsTalking.png"></div>
            
        </div>
    </div>
</body>
</html>
