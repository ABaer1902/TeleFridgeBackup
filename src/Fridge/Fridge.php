
<?php
include '../GeneralBackEnd/ServerChecks.php';

//check user associated with cookie id (returns csv for now)
function retrieveFridge($cookieid) {
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

    //query
    $cookieQuery = "SELECT fridge FROM accounts WHERE `cookie`=?";
    $stmt = $conn->prepare($cookieQuery);
    $stmt->bind_param('s', $cookieid);
    $stmt->execute();
    $stmt->bind_result($fridgeString);
    $stmt->fetch();

    //parse fridge string
    //$fridgeItems = str_getcsv($fridgeString);

    //close connection
    $stmt->close();
    $conn->close();

    return $fridgeString;
}

//update SQL
function fridge_update($cookieid, $fridgeString) {
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

    //query
    $fridgeInsert = "UPDATE accounts SET fridge=? WHERE cookie=?";
    $stmt = $conn->prepare($fridgeInsert);
    $stmt->bind_param('ss', $fridgeString, $cookieid);
    if ($stmt->execute()) {
        $conn->close();
        return true;
    }
    echo "Error: " . $sql . "<br>" . $stmt->error;
    exit;
}

//add ingredient
function fridge_add($cookieid, $item) {
    $fridgeItems = retrieveFridge($cookieid);
    if (in_array($item, $fridgeItems))
        return false;
    array_push($fridgeItems, $item);
    $fridgeString = implode(',', $fridgeItems);
    return fridgeupdate($cookieid, $fridgeString);
}

//remove ingredient
function fridge_remove($cookieid, $item) {
    $fridgeItems = retrieveFridge($cookieid);
    if (!in_array($item, $fridgeItems))
        return false;
    if (($key = array_search($item, $fridgeItems)) !== false) {
        unset($fridgeItems[$key]);
    }
    $fridgeString = implode(',', $fridgeItems);
    return fridgeupdate($cookieid, $fridgeString);
}

// ACTUAL LOGIC -------------------

// first check if user is logged in
checkLoggedIn();

$profile_picture = "../imgs/defaultProfilePic.jpg";
if (!isGuest()){

    $user_id = get_user_id();
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

// handle a list of ingredients
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fridgeString = htmlspecialchars($_POST['ingredients']);
    fridge_update($_COOKIE['username'], $fridgeString);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Fridge</title>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="FrontEnd/App.js"></script>
</head>

<body onload="startFunction()">

    <!-- Start Function-->
    <?php
        if (isset($_COOKIE['username']))
            $cookieid = $_COOKIE['username'];
        $fridgeString = retrieveFridge($cookieid);
        $fridgeItems = str_getcsv($fridgeString); 
    ?>
    <script>
        function startFunction() {
            var ingredientList = document.getElementById("ingredientUl");
            <?php foreach ($fridgeItems as $item) {
                ?>
                var li = document.createElement("li");
                li.textContent = '<?php echo $item ?>';
                li.id = '<?php echo $item ?>';
                li.setAttribute("class", "ingredient");
                li.setAttribute("onclick", "removeIngredient('<?php echo $item ?>')");
                ingredientList.appendChild(li);
                <?php
            } ?>
            updateIngredientList();
        }
    </script>
    
    <!-- Navigation Bar Structure-------------------------------------------->
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">

        <form id="fridgelist" action="" method="post">
            <div id="pageContents">
                <div id="titleContainer">
                    <div id="titletop">
                        <div><button id="backButton" class="button" type="button" onclick="history.back()"> <img class="image" id="backImg" src="FrontEnd/imgs/back_arrow.png" alt="Back"> </button></div>
                        <div><h1 class="message" id="title">My Fridge</h1></div>
                    </div>
                    <div id="titlebottom"><p class="message" id="instruction">Click on any Ingredient to remove</p></div>
                </div>

                <div id="fridgeLining">
                        <p class="message" id="ingredientsPrompt">Ingredients:</p>
                        <div id="ingredientControl">
                            <input type="text" id="ingredientInput" placeholder="Enter ingredient...">
                            <div class="topButtonContainer"><button type="button" class="button" class="smallButton" class="left" id="+Button" onclick="addIngredient()"> <img class="smallImage" id="setImg" src="FrontEnd/imgs/+.png" alt="Set"> </button></div>
                            <div class="topButtonContainer"><button type="button" class="button" class="smallButton" class="right" id="removeButton" onclick="removeLastIngredient()"> <img class="smallImage" id="removeImg" src="FrontEnd/imgs/remove.png" alt="Remove"> </button></div>    
                        </div>

                <div id="fridgeInterior">

                    <div id="frdgeContents">
                        <!-- container to display ingredients -->
                        <div id="ingredientList">
                            <ul id="ingredientUl"></ul>
                        </div>
                        
                        
                        <input type="hidden" name="ingredients" id="ingredientListInput" value="">

                    </div>

                </div>
                </div>

                <div id="ingButtons">
                    <div class="bigButtonContainerL"><button id="submitButton" class="button" class="left" type="submit"> <img class="image" id="submitImg" src="FrontEnd/imgs/confirm.png" alt="Confirm"> </button></div>
                    <div class="bigButtonContainerC"><button id="cancelButton" class="button" class="right" type="button" onclick="location.reload()"> <img class="image" id="cancelImg" src="FrontEnd/imgs/cancel.png" alt="Cancel"> </button></div>
                    <div class="bigButtonContainerR"><button id="clearButton" class="button" class="clear" type="button"> <img class="image" id="clearImg" src="FrontEnd/imgs/clear.png" alt="Clear"> </button></div>
                </div>

            </div>
        </form>
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
