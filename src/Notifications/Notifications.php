<?php
include '../GeneralBackEnd/ServerChecks.php';

// Check logged in and get user_id
checkLoggedIn();
$user_id = get_user_id();

// Create database connection

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

// Get all notifications for the user
// Get likes/dislikes data
$sql = "SELECT * FROM likes_dislikes WHERE creator_id=$user_id";
$result = mysqli_query($conn, $sql);
$like_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Get saves data
$sql = "SELECT * FROM saves WHERE creator_id=$user_id";
$result = mysqli_query($conn, $sql);
$save_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

// Get follower data
$sql = "SELECT follower_id, timestamp FROM followers WHERE following_id=$user_id";
$result = mysqli_query($conn, $sql);
$following_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$social_data = array();

foreach ($like_data as $i => $data) {
    // Get profile name
    $profile_id = $data["user_id"];
    $sql = "SELECT username, profile_picture FROM accounts WHERE id=$profile_id";
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $profilename = $res[0]["username"];
    $profilepic = $res[0]["profile_picture"];
    mysqli_free_result($result);

    $reaction = $data["reaction"];
    $profile_url = "../Profile/Profile.php?profile_id=$profile_id";
    $recipe_id = $data['recipe_id'];
    $recipe_url = "../Recipe/Recipe.php?recipe_id=$recipe_id";

    $sql = "SELECT picture FROM recipes WHERE id=$recipe_id";
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $rpic = $res[0]["picture"];
    mysqli_free_result($result);

    if ($reaction == 1) {
        // liked
        $str = "<a href='$profile_url'>" . $profilename . "</a>" . " liked your " . "<a href='$recipe_url'>" . "recipe" . "</a>";
    }
    else if ($reaction == -1) {
        // disliked
        $str = "<a href='$profile_url'>" . $profilename . "</a>" . " disliked your " . "<a href='$recipe_url'>" . "recipe" . "</a>";
    }

    if ($reaction == 1 or $reaction == -1) {
        // Add array to social_data
        array_push($social_data, array("notification" => $str, "timestamp" => $data["timestamp"], "rpic" => $rpic, "pfp" => $profilepic, "profile" => $profile_url, "recipe" => $recipe_url));
    }
}


foreach ($save_data as $i => $data) {
    // Get profile name
    $profile_id = $data["user_id"];
    $sql = "SELECT username, profile_picture FROM accounts WHERE id=$profile_id";
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $profilename = $res[0]["username"];
    $profilepic = $res[0]["profile_picture"];
    mysqli_free_result($result);

    $profile_url = "../Profile/Profile.php?profile_id=$profile_id";
    $recipe_id = $data['recipe_id'];
    $recipe_url = "../Recipe/Recipe.php?recipe_id=$recipe_id";

    $sql = "SELECT picture FROM recipes WHERE id=$recipe_id";
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $rpic = $res[0]["picture"];
    mysqli_free_result($result);

    $str = "<a href='$profile_url'>" . $profilename . "</a>" . " saved your " . "<a href='$recipe_url'>" . "recipe" . "</a>";

    array_push($social_data, array("notification" => $str, "timestamp" => $data["timestamp"], "rpic" => $rpic, "pfp" => $profilepic, "profile" => $profile_url, "recipe" => $recipe_url));
}


// get user profile picture
$profile_picture = "../imgs/defaultProfilePic.jpg";
if (!isGuest()){

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
}


foreach ($following_data as $i => $data) {
    // Get profile name
    $profile_id = $data["follower_id"];
    $sql = "SELECT username, profile_picture FROM accounts WHERE id=$profile_id";
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $profilename = $res[0]["username"];
    $profilepic = $res[0]["profile_picture"];
    mysqli_free_result($result);

    $profile_url = "../Profile/Profile.php?profile_id=$profile_id";

    
    $sql = "SELECT profile_picture FROM accounts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rpic = mysqli_fetch_assoc($result)["profile_picture"];
    mysqli_free_result($result);
    

    $str = "<a href='$profile_url'>" . $profilename . "</a>" . " started following you";

    array_push($social_data, array("notification" => $str, "timestamp" => $data["timestamp"], "rpic" => $rpic, "pfp" => $profilepic, "profile" => $profile_url, "recipe" => "../Profile/Profile.php"));
}


function compareTimestamps($a, $b) {
    $timestampA = strtotime($a['timestamp']);
    $timestampB = strtotime($b['timestamp']);
    return $timestampB - $timestampA; // Reversed comparison
}

usort($social_data, 'compareTimestamps');

//print_r($social_data);

//notes for future Kelvin: replace pretty much every instance of recipes or related stuff with new incoming data

$conn->close();
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
                    <h1 id="title">&nbsp;Notifications</h1>
                </div>

                <div id="recipes">

                    <?php 
                    
                    //display
                    foreach ($social_data as $i => $data) {
                        if ($social_data[$i]['pfp'] != '') {
                            $pic = base64_encode($social_data[$i]["pfp"]);
                            $pfp = 'data:image/jpeg;base64,' . $pic;
                        } else {
                            $pfp = "..\imgs\defaultProfilePic.jpg";
                        }
                        echo "<div class=\"singleRecipe\">
                            <div id=\"profilePicContainer\"> <a href='" . $social_data[$i]['profile'] . "'><img id=\"profilePic\" src=$pfp></a>
                            </div>";

                        echo "<p id=\"recipeName\">";
                        echo $social_data[$i]["notification"];
                        echo "</p>";
                        if ($social_data[$i]['rpic'] != '') {
                            $pic = base64_encode($social_data[$i]["rpic"]);
                            $rpic = 'data:image/jpeg;base64,' . $pic;
                        } else {
                            $rpic = "..\imgs\defaultRecipePic.png";
                        }
                        echo "<div id=\"recipePicContainer\"> <a href='" . $social_data[$i]['recipe'] . "'><img id=\"recipePic\" src=$rpic> </a></div>";
                        
                        echo "</div>";

                        /*

                        echo "<div id=\"recipeName\"> <a id=\"recipeInfo\" href="; 
                        echo $like_data[$i]["recipe_id"];
                        echo ">";

                        echo $like_data[$i]["recipe_id"];
                        echo "</a> </div>";

                        echo "<p>&nbsp;has been&nbsp;";

                        if ($like_data[$i]["reaction"] == 1)
                            echo "liked";
                        else
                            echo "disliked";

                        echo "&nbsp;by&nbsp;";
                        echo $like_data[$i]["user_id"];
                        echo "</p> </div>";

                        echo "<div id=\"difficulty\">";
                        
                        for ($x=1; $x <= $like_data[$i]["difficulty"]; $x++){
                            echo "<img class=\"icon\" src=\"FrontEnd/imgs/yellow_star.png\">";
                        }
                       
                        echo "</div> <div id=\"likes\"> <img class=\"icon\" src=\"FrontEnd/imgs/heart_filled.png\"><p>";
                        echo $like_data[$i]["likes"];
                        echo "</p> </div> 
                        <div id=\"dislikes\"> <img class=\"icon\" src=\"FrontEnd/imgs/broken_heart_filled.png\"><p>";
                        echo $like_data[$i]["dislikes"];
                        echo "</p> </div> </div>";
                        */
                
                    }


                ?>

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
                <a class="tabNB" href="../Profile/Profile.php" > <div class="buttonNB" id="profileContainerNB">
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