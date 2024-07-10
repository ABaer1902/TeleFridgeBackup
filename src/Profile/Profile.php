<?php 
include '../GeneralBackEnd/ServerChecks.php';


// Check logged in and get user_id
checkLoggedIn();
$user_id = get_user_id();


// pull data for the profile page here

session_start();

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


if (isset($_GET["profile_id"])){
    $profile_id = $_GET["profile_id"];
    $_SESSION['profile_id'] = $_GET["profile_id"];
}

else {
    $profile_id = $user_id;
}


$_SESSION['permission'] = 0;
// set permissions
if ($user_id == $profile_id){
    $_SESSION['permission'] = 1;
}


$profile_link = "../Profile/Profile.php?user_id=$user_id&profile_id=$user_id";

// Get username and profile picure
$sql = "SELECT username, profile_picture FROM accounts WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $profile_id);
mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);

if($row = mysqli_fetch_assoc($result)){
    $username = $row['username'];
    
    if ($row["profile_picture"] != '') {
        $pic = base64_encode($row["profile_picture"]);
        $profile_picture = 'data:image/jpeg;base64,' . $pic;
    } else {
        // Use default profile picture if profile picture not found
        $profile_picture = "../imgs/defaultProfilePic.jpg";
    }

} else {
    $username = "User Not Found";
    $profile_picture = "../imgs/defaultProfilePic.jpg";
}

// Free result and close statement
mysqli_free_result($result);
mysqli_stmt_close($stmt);

// Get the logged in users proifle picture for display on the nav bar
$sql = "SELECT username, profile_picture FROM accounts WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user_profile_picture = mysqli_fetch_assoc($result)["profile_picture"];

if ($user_profile_picture != ''){
    $pic = base64_encode($user_profile_picture);
    $user_profile_picture = 'data:image/jpeg;base64,' . $pic;
}

else {
    $user_profile_picture = "../imgs/defaultProfilePic.jpg";
}

mysqli_free_result($result);
mysqli_stmt_close($stmt);

$sql = "SELECT id, likes, dislikes, name, difficulty, picture, calories FROM recipes WHERE creator_id = $profile_id";

$result = mysqli_query($conn, $sql);
$recipe_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

foreach ($recipe_data as $i => $data) {
    $recipe_data[$i]["recipe_link"] = "../Recipe/Recipe.php?recipe_id={$data['id']}";
}

//followers list
$sql = "SELECT accounts.profile_picture, accounts.username, accounts.id
        FROM accounts
        INNER JOIN followers ON accounts.id=followers.follower_id
        WHERE followers.following_id=$profile_id";
$result = mysqli_query($conn, $sql);
$follow_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
$follow_count = mysqli_num_rows($result);
mysqli_free_result($result);

$sql = "SELECT COUNT(follower_id) FROM followers WHERE following_id = $profile_id AND follower_id = $user_id";
$result = mysqli_query($conn, $sql);
$followed = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(follower_id)'];
mysqli_free_result($result);


//following list
$sql = "SELECT accounts.profile_picture, accounts.username, accounts.id
        FROM accounts
        INNER JOIN followers ON accounts.id=followers.following_id
        WHERE followers.follower_id=$profile_id";
$result = mysqli_query($conn, $sql);
$following_list = mysqli_fetch_all($result, MYSQLI_ASSOC);
$following_count = mysqli_num_rows($result);
mysqli_free_result($result);

$sql = "SELECT COUNT(following_id) FROM followers WHERE follower_id = $profile_id AND following_id = $user_id";
$result = mysqli_query($conn, $sql);
$following = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(following_id)'];
mysqli_free_result($result);


if ($user_id == $profile_id) {
    $followed = 0;
}

//POST for follower button
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['follow']) || isset($_POST['unfollow'])) {
        if (isset($_POST['follow'])) {
            $follow_req = "INSERT INTO followers (follower_id, following_id) VALUES (?, ?)";
        }
        if (isset($_POST['unfollow'])) {
            $follow_req = "DELETE FROM followers WHERE follower_id=? AND following_id=?";
        }

    }

    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare($follow_req);
    $stmt->bind_param("ii", $user_id, $profile_id);
    $stmt->execute();

    header("Refresh:0");
}

// getting the bio
$bio = NULL;
$sql = "SELECT bio FROM accounts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $profile_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $bio = $row['bio'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="FrontEnd/App.js"></script>
</head>

<body>

    
    <!-- Navigation Bar Structure-------------------------------------------->
    
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">
            <div id="pageContents">
                <div id="info">
                    <a class="button" id="backButton" href="javascript:history.back()"> <img src="FrontEnd/imgs/back_arrow.png" alt="<---" /> </a>
                    <div id="picContainer">

                        <?php 
                        
                        if($_SESSION['permission'] == 1){
                            echo '<button id="picbtn">';
                            echo "<img id='pic' src="; echo $profile_picture; echo ">";
                            echo "</button>";
                        } else {
                            echo "<img id='pic' src="; echo $profile_picture; echo ">";
                        }
                        ?> 
                        
                    </div>

                    <div id="nameContainer">

                        <?php 
                        
                        if($_SESSION['permission'] == 1){
                            echo "<button id='username'>";
                            echo "<h1> <div class='username_font'>";
                            echo $username;
                            echo "</h1>";
                            echo "</button>";

                        } else{
                            echo "<h1> <div class='username_font'>";
                            echo $username;
                            echo "</h1>";
                        }
                        ?>

                    </div>

                    <div id="followers">
                        <form id="follow" method="post">
                            <button id='follow-button' style='<?php if ($_SESSION['permission'] == 0) { echo 'display: block;'; } else { echo 'display: none;';}?> <?php if ($followed == true) { echo 'background-color: #FBAE3B;';} else { echo 'background-color: #1f66ff;';} ?>' 
                                name='<?php if ($followed == true) { echo "unfollow"; } else { echo "follow"; }?>'>
                                <img id='fpic' src=<?php if($followed == true) { echo 'FrontEnd/imgs/followed.png'; } else { echo 'FrontEnd/imgs/follow.png'; } ?> >
                            </button>
                        </form>
                        <?php if ($_SESSION['permission'] != 0) {
                            echo "<h1 id='display-follow'>Followers: </h1> "; 
                        }
                        ?>

                        <?php if($_SESSION['permission'] != 0){
                            echo "<button id='show-follow'>";
                            }
                        ?>
                            <h1> <?php echo $follow_count ?> </h1>
                        
                        <?php if($_SESSION['permission'] != 0){
                            echo "</button>";
                            }
                        ?>

                        <?php if ($_SESSION['permission'] != 0) {
                            echo "<h1 id='display-follow'>Following: </h1> "; 
                        }
                        ?>



                        <button id="show-following"> 
                            <h1> <?php if ($_SESSION['permission'] != 0) {
                                    echo $following_count; 
                                }
                            ?> 
                        </h1>
                        </button>
                    </div>
                </div>

                <!-- bio stuff -->
                <div id="bioContainer">
                    <form id="bioForm" method="post" action="BackEnd/NewBio.php">
                        <label for="bio">Bio:</label><br>
                        <?php if ($user_id == $profile_id): ?>
                            <textarea id="bioTextarea" class="message" name="bio" rows="4" cols="50" style="resize: none; background-color: #d3d3d3;"><?php echo $bio ?></textarea>
                            <div id="bioButtons">
                                <div id="bioButton">
                                <button type="button" id="saveBtn" name="save"><img class="smallImage" id="saveImg" src="FrontEnd/imgs/save.png" alt="Save"></button>
                                <button type="button" id="cancelBtn" name="cancel"><img class="smallImage" id="cancelImg" src="FrontEnd/imgs/cancel.png" alt="Cancel"></button>
                                </div>
                            </div>
                        <?php else: ?>
                            <textarea id="bioTextarea" class="message" name="bio" rows="4" cols="50" style="resize: none; background-color: #d3d3d3;" readonly><?php echo $bio ?></textarea>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div id="recipeTitle">
                    <h1><?php if ($user_id == $profile_id) echo "Your";?> Recipes</h1>
                </div>

                <div id="recipes">

                    <?php 
                    
                    foreach ($recipe_data as $i => $data) {
                        echo "<div class=\"singleRecipe\">";
                        $picture = "../imgs/defaultRecipePic.png";
                        if ($recipe_data[$i]['picture'] != '') {
                            $pic = base64_encode($recipe_data[$i]['picture']);
                            $picture = "'data:image/jpeg;base64, $pic'";
                        }
                        echo "<div id=\"recipePicContainer\"> <img id=\"recipePic\" src={$picture} ></div>";

                        echo "<div id=\"recipeName\"> <a id=\"recipeInfo\" href="; 
                        echo $recipe_data[$i]["recipe_link"];
                        echo ">";

                        echo $recipe_data[$i]["name"];
                        echo "</a> </div>";

                        // Echo three dots for link to edit recipe
                        if ($_SESSION['permission']){
                            echo "<div id='editPicture'> <a href='../EditRecipe/EditRecipe.php?recipe_id=";
                            echo $recipe_data[$i]['id'];
                            echo "'> <img id='dots-picture' src='FrontEnd/imgs/edit.png' alt='Edit/Delete'> </a> </div>";
                        }

                        echo "<div id=\"difficulty\">";
                        
                        for ($x=1; $x <= $recipe_data[$i]["difficulty"]; $x++){
                            echo "<img class=\"icon\" src=\"FrontEnd/imgs/yellow_star.png\">";
                        }
                        
                        echo "</div> <div id=\"calories\">";
                        echo "<p>Calories: " . $recipe_data[$i]["calories"] . "</p>";
                       
                        echo "</div> <div id=\"likes\"> <img class=\"icon\" src=\"FrontEnd/imgs/heart_filled.png\"><p>";
                        echo $recipe_data[$i]["likes"];
                        echo "</p> </div> 
                        <div id=\"dislikes\"> <img class=\"icon\" src=\"FrontEnd/imgs/broken_heart_filled.png\"><p>";
                        echo $recipe_data[$i]["dislikes"];
                        echo "</p> </div> </div>";
                
                    }

                ?>

                </div>

                <!-- HTML form for the logout button -->
                <?php if ($user_id == $profile_id): ?>
                    <div id="logoutContainer" onclick="logout()">
                    <img id="logoutIcon" src="FrontEnd/imgs/logout.png">
                    <p class="message" id="logoutMessage">Logout</p>
                    </div>
                <?php endif; ?>                

                
            </div>


        </div>
        
        <!-- Popup for IMAGE CHANGE -->
        <div id="popupOverlay" class="overlay-container"> 
            <div class="popup-box"> 
                <h2 style="color: #FBAE3B;">Update Profile Picture</h2> 
                <form id="ChangePicForm" class="form-container" action="BackEnd/Change_Profile_Picture.php" method="post" enctype="multipart/form-data">
                    <br>
                    <label class="form-label">      
                    Upload New Photo Here:
                    <br>
                    <br>
                    (JPG or PNG, < 65KB) 
                    </label> 
                    <input type="file" id="form-input" name="image" accept="image/png, image/jpeg" required>
                    <input type="hidden" name="permission" value=<?php echo $_SESSION['permission'] ?>>
                    <input type="hidden" name="profile_id" value=<?php echo $profile_id ?>>
                    <br>
                    <br>
                    <button class="btn-submit" type="submit"> Submit </button> 
                </form> 
                <button class="btn-close-popup" onclick="togglePopup()"> 
                Close 
                </button>

            </div> 
        </div>   
        
        
        <!-- Popup for USERNAME CHANGE -->
        <div id="popupOverlayN" class="overlay-containerN"> 
            <div class="popup-boxN"> 
                <h2 style="color: #FBAE3B;">Update Username</h2> 
                <form id="ChangeNameForm" class="form-containerN" action="BackEnd/Change_Username.php" method="post"" enctype="multipart/form-data">
                    <br>
                    <label class="form-labelN" 
                        for="username">      
                        
                    Enter New Username Here:
                    <br>
                    <br>
                    </label> 
                    <input type="text" id="form-inputN" name="username" required>
                    <input type="hidden" name="permission" value=<?php echo $_SESSION['permission'] ?>>
                    <input type="hidden" name="profile_id" value=<?php echo $profile_id ?>>
                    <br>
                    <br>
                    <button class="btn-submitN" type="submit"> Submit </button> 
                </form> 
                <button class="btn-close-popupN" onclick="togglePopupN()"> 
                Close 
                </button>

            </div> 
        </div>  


        <!-- Popup for followers -->
        <div id="popupOverlayF" class="overlay-containerF"> 
            <div class="popup-boxF"> 
                <h2 style="color: #FBAE3B;">Followers</h2> 
                <br>
                <br>
                <div id="follow-list">
                    <?php
                    foreach ($follow_list as $row) {
                        if ($row['profile_picture'] != '') {
                            $pic = base64_encode($row["profile_picture"]);
                            $pfp = 'data:image/jpeg;base64,' . $pic;
                        } else {
                            $pfp = "../imgs/defaultProfilePic.jpg";
                        }
                        ?>
                        
                        <button onclick="change_profile(<?php echo $row['id']?>)" style="border: none; background: none; cursor:pointer;"><img id="flpic" src=<?php echo $pfp ?>>
                        <h1 id="flname"><?php echo $row['username']?></h1></button>
                        <?php
                    }
                    ?>
                    
                    <!-- foreach person in follower list -->

                </div>


                <button class="btn-close-popupF" onclick="togglePopupF()"> 
                Close 
                </button>

            </div> 
        </div> 



        <!-- Popup for follwing -->
        <div id="popupOverlayFL" class="overlay-containerFL"> 
            <div class="popup-boxFL"> 
                <h2 style="color: #FBAE3B;">Following</h2> 
                <br>
                <br>
                <div id="following-list">
                    <?php
                    foreach ($following_list as $row) {
                        if ($row['profile_picture'] != '') {
                            $pic = base64_encode($row["profile_picture"]);
                            $pfp = 'data:image/jpeg;base64,' . $pic;
                        } else {
                            $pfp = "../imgs/defaultProfilePic.jpg";
                        }
                        ?>
                        
                        <button onclick="change_profile(<?php echo $row['id']?>)" style="border: none; background: none; cursor:pointer;"><img id="flpic" src=<?php echo $pfp ?>>
                        <h1 id="flname"><?php echo $row['username']?></h1></button>
                        <?php
                    }
                    ?>
                    
                    <!-- foreach person in follower list -->

                </div>


                <button class="btn-close-popup" onclick="togglePopupFL()"> 
                Close 
                </button>

            </div> 
        </div> 

        <script>
            function change_profile(user_id) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('profile_id', user_id);
                window.history.pushState({}, '', currentUrl);
                window.location.reload();
            }
        </script>


        <!-- Navigation Bar -->
        <div id="navBar">
            <!-- Need to allow ability to take image from database -->
            <div id="pictureContainerNB"> <img id="profilePicNB" src=<?php echo $user_profile_picture; ?>> </div>

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
