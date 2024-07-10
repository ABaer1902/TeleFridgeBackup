<?php
include '../GeneralBackEnd/ServerChecks.php';

checkLoggedIn();

// get user id
$user_id = get_user_id();

if ($_SERVER["REQUEST_METHOD"] === "GET") { 
    // get creator id
    $creator_id = htmlspecialchars($_GET['creator_id']);
    $collection_id = htmlspecialchars($_GET['collection_id']);

    // check if they are logged in and if the logged in user == the creator of that collection
    if ($user_id != $creator_id) {
        // Redirect to their collections page
        header("Location: Collections.php");
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

    // get collection name
    $sql = "SELECT name FROM collections WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $collection_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) { 
        $row = $result->fetch_assoc();
        $collection_name = $row['name'];
    } else {
        $collection_name = "no collection found";
    }

    $profile_picture = "../imgs/defaultProfilePic.jpg";
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
    

    // echo 'CREATOR ID: ' . $user_id;
    // echo 'COLLECTION ID: ' . $collection_id;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="FrontEnd/SingleCollection.js"></script>
</head>

<body>
    
    
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">

        <div id="titleContainer" class="flex-container">
            <div class="title-wrapper">
                <h1 class="message" id="title"><?php echo $collection_name; ?></h1>
                <a class="message" id="otherLink" href="Collections.php">go to collections</a>
            </div>
            <button id="delete-collection-button" class="transparent-btn">
                <img id="deleteCollectionImage" src="FrontEnd/imgs/delete.png" alt="Delete">
            </button>
        </div>

            <div id="pageContents">    
                
            
            <div id="myModal" class="modal">
    <div class="modal-content">
        <p class="message" id="smallMessage">Would you like to view the recipe or remove it from the collection?</p>
        <div class="button-container">
            <button id="viewButton" class="transparent-btn"><img id="viewRecipeImage" src="FrontEnd/imgs/view-button.png" alt="View"></button>
            <button id="removeButton" class="transparent-btn"><img id="removeRecipeImage" src="FrontEnd/imgs/remove-button.png" alt="Remove"></button>
        </div>
    </div>
</div>

                    
                <div class="allSavedPosts"> 
                <?php include 'BackEnd/SingleCollectionsSaves.php'; ?>
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
    </div>
</body>
</html>
