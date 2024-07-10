<?php
include '../GeneralBackEnd/ServerChecks.php';


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

// first check if user is logged in
checkLoggedIn();


$user_id = get_user_id();


function get_meal_plan($day){
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
    $user_id = get_user_id();
    $sql = "SELECT meal_id, user_id, recipe_id, day FROM meal_plan WHERE user_id = $user_id AND day = '$day'";

    $result = mysqli_query($conn, $sql);
    $meal_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    foreach ($meal_data as $i => $data){
        $recipe_id = $meal_data[$i]['recipe_id'];
        $sql = "SELECT id, hours, minutes, name, picture FROM recipes WHERE id = $recipe_id";
        $result = mysqli_query($conn, $sql);
        $recipe_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        
        // recipe picture <img id="pic" src=...>
        $picture = "../imgs/defaultRecipePic.png";
        if ($recipe_data[0]['picture'] != '') {
            $pic = base64_encode($recipe_data[0]['picture']);
            $picture = "'data:image/jpeg;base64, $pic'";
        }
        $meal_id = $meal_data[$i]['meal_id'];
        
        echo "<a class='recipeLink' style='text-decoration: none; color:black;' href='../Recipe/Recipe.php?recipe_id={$recipe_id}'>";
        echo "<div class='recipe'>";
        echo "<div class='picture'><img id='pic' src={$picture} ></div>";

        //recipe name <h1>name</h1>
        echo "<div class='details'>";
        echo "<h1>";
        echo $recipe_data[0]['name'];
        echo "</h1>";

        //recipe time <p id="time">0H 0M</p>
        echo "<p style='color:black;' id=\"time\">";
        echo $recipe_data[0]['hours'];
        echo "H ";
        echo $recipe_data[0]['minutes'];
        echo "M</p>";
        echo "<form action='MealPlan.php' method='post'>";
        echo "<input type='hidden' name='del_meal_id' value=$meal_id>";
        echo "<button style='background: transparent; border-width: 0px;' id='rmv' type='submit'> <img style='width: 40px;' src='FrontEnd/imgs/delete.png'> </button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
        echo "</a>";
    }

}

$profile_picture = "../imgs/defaultProfilePic.jpg";
if (!isGuest()){

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
}


if(isset($_POST['del_meal_id'])) { 
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
    $meal_id = $_POST['del_meal_id'];
    $user_id = get_user_id();
    $sql ="DELETE FROM meal_plan WHERE meal_id = ?";
    $stmt = $conn->prepare($sql);
        // Bind parameters
    $stmt->bind_param("i", $meal_id);
        // execute the query
    $stmt->execute();

} 


?>

<!DOCTYPE html>
<html>
<head>
    <title>Meal Plan</title>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="FrontEnd/App.js"></script>
</head>

<body>
    
    
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">

            <div id="titleContainer">
                <a class="button" id="backButton" href="javascript:history.back()"> <img src="FrontEnd/imgs/back_arrow.png" alt="<---" /> </a>
                <h1 class="message" id="title">Weekly Meal Plan</h1>
            </div>
            <div id="pageContents">
                <div id="schedule"> 
                    <div class="row" id ="sunday"><div class="label">Sunday</div><div class="content"><?php get_meal_plan("sunday") ?></div></div>
                    <div class="row" id ="monday"><div class="label">Monday</div><div class="content"><?php get_meal_plan("monday") ?></div></div>
                    <div class="row" id ="tuesday"><div class="label">Tuesday</div><div class="content"><?php get_meal_plan("tuesday") ?></div></div>
                    <div class="row" id ="wednesday"><div class="label">Wednesday</div><div class="content"><?php get_meal_plan("wednesday") ?></div></div>
                    <div class="row" id ="thursday"><div class="label">Thursday</div><div class="content"><?php get_meal_plan("thursday") ?></div></div>
                    <div class="row" id ="friday"><div class="label">Friday</div><div class="content"><?php get_meal_plan("friday") ?></div></div>
                    <div class="row" id ="saturday"><div class="label">Saturday</div><div class="content"><?php get_meal_plan("saturday") ?></div></div>
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
