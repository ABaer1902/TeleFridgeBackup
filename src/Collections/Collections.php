<?php
include '../GeneralBackEnd/ServerChecks.php';

// first check if user is logged in
checkLoggedIn();

// get user id
$user_id = get_user_id();
$profile_picture = "../imgs/defaultProfilePic.jpg";
if (!isGuest()){

    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);

    // check if connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT profile_picture FROM accounts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if($row = mysqli_fetch_assoc($result)){
        
        if ($row["profile_picture"] != '') {
            $pic = base64_encode($row["profile_picture"]);
            $profile_picture = 'data:image/jpeg;base64,' . $pic;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saves</title>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="FrontEnd/Collections.js"></script>
</head>

<body>
    
    
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">

            <div id="titleContainer">
                <div id="toptitle">
                    <div><h1 class="message" id="title">Collections</h1></div>
                    <div><button id="new-collection-button"> <image id="newCollectionImage" src="FrontEnd/imgs/+Collection.png"> </button></div>
                </div>
                <a class="message" id="otherLink" href="../Saves/Saves.php">go to saves</a>
                <!-- create new collection button -->
            </div>

                <!-- Popup modal -->
                <div id="popup-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 class="message" style="color: #FBAE3B">Create New Collection</h2>
                    <input class="message" type="text" id="collection-name" placeholder="Enter collection name">
                    <button class="button" id="create-collection-btn">Create</button>
                </div>
                </div>

            
            <div id="pageContents">
                <div class="allCollections">    
                <div id="collectionList">
                <?php include 'BackEnd/GeneralCollections.php'; ?>
                </div>
            </div>
            </div>

        </div>
        
        <!-- Navigation Bar -->
        <div id="navBar">
            <!-- Need to allow ability to take image from database -->
            <div id="pictureContainerNB"> <img id="profilePicNB" src=<?php echo $profile_picture; ?>> </div>

            <div id="buttonCollectionNB">
                <a class="tabNB" href="../Home/Home.php"> <div class="buttonNB" id="homeContainerNB">
                    <img class="iconNB" id="homeButtonNB" src="../imgs/navbar/home.png" alt="Home">
                    <div class="messageContainerNB"><p class="messageNB" >Home</p></div>
                </div></a>
                <a class="tabNB" href="../Profile/Profile.php"> <div class="buttonNB" id="profileContainerNB">
                    <img class="iconNB" id="profileButtonNB" src="../imgs/navbar/profile.png" alt="Profile">
                    <div class="messageContainerNB"><p class="messageNB" >Profile</p></div>
                </div></a>
                <a class="tabNB" href="../Saves/Saves.php"> <div class="buttonNB" id="savesContainerNB">
                    <img class="iconNB" id="savesButtonNB" src="../imgs/navbar/saves.png" alt="Saves">
                    <div class="messageContainerNB"><p class="messageNB" >Saves</p></div>
                </div></a>
                <a class="tabNB" href="../Notifications/Notifications.php"> <div class="buttonNB" id="notiContainerNB">
                    <img class="iconNB" id="notiButtonNB" src="../imgs/navbar/notifications.png" alt="Notifications">
                    <div class="messageContainerNB"><p class="messageNB" >Social</p></div>
                </div></a>  
                <a class="tabNB" href="../MealPlan/MealPlan.php"> <div class="buttonNB" id="planContainerNB">
                    <img class="iconNB" id="planButtonNB" src="../imgs/navbar/mealPlan.png" alt="Meal Plan">
                    <div class="messageContainerNB"><p class="messageNB" >Meal Plan</p></div>
                </div></a>
                <a class="tabNB" href="../NewRecipe/NewRecipe.php"> <div class="buttonNB" id="uploadContainerNB">
                    <img class="iconNB" id="uploadButtonNB" src="../imgs/navbar/upload.png" alt="New Recipe">
                    <div class="messageContainerNB"><p class="messageNB" >New Recipe</p></div>
                </div></a>
                <a class="tabNB" href="../Fridge/Fridge.php"> <div class="buttonNB" id="fridgeContainerNB">
                    <img class="iconNB" id="fridgeButtonNB" src="../imgs/navbar/fridge.png" alt="My Fridge">
                    <div class="messageContainerNB"><p class="messageNB" >My Fridge</p></div>
                </div></a>
            </div>


            <div class="tabNB" id="titleContainerNB">
                <h1 id="titleNB">Tele-fridge</h1>
            </div></a>
        </div>
        <!--------------------------------------------------------->
    
        <!-- Popup for ERRORS -->
        <div id="popupOverlay" class="overlay-container"> 
            <div class="popup-box"> 
                <h3 style="color: #FBAE3B;">WARNING</h3> 

                <p id="error"> Collection name cannot be empty or contain space characters. </p>
                <br>

                <button class="btn-close-popup" onclick="togglePopup();"> 
                    Close
                </button>
            </div> 
        </div> 
    
    
    </div>
</body>
</html>
