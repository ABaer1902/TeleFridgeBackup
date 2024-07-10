<?php
include '../GeneralBackEnd/ServerChecks.php';

// first check if user is logged in


// php file for processing  
require_once 'BackEnd/EditRecipeStorage.php';

checkLoggedIn();
$user_id = get_user_id();

session_start();

// Grabbing cookies
if (isset($_GET["recipe_id"])){
    $recipe_id = $_GET["recipe_id"];
    $_SESSION['recipe_id'] = $recipe_id;
}

// Connect to the database
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

// Get the Info from the database here
$profile_page_link = "../Profile/Profile.php";

$sql = "SELECT name, difficulty, hours, minutes, description, instructions, ingredients, creator_id, picture, calories FROM recipes WHERE id = $recipe_id";
$result = mysqli_query($conn, $sql);
$recipe_data = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
mysqli_free_result($result);

$creator_id = $recipe_data["creator_id"];

// if user logged in did not create this recipe send them back to profile
if ($creator_id != $user_id) {
    header('Location: ../Profile/Profile.php');
}

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

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="FrontEnd/App.js"></script>
</head>

<body onload="startFunction(), difficulty_button(<?php echo $recipe_data['difficulty']; ?>)">

    <!-- Start Function (Yeah I Know, This is Weird) -->
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
        function startFunction() {
            document.getElementById('recipe-calories').value = "<?php echo $recipe_data['calories']; ?>";
            var input = document.getElementById("ingredientListInput");
            input.value = "<?php echo $recipe_data['ingredients']; ?>";
            var ingredientList = document.getElementById("ingredientUl");
            <?php foreach (explode(',', $recipe_data['ingredients']) as $item) {
                ?>
                var li = document.createElement("li");
                li.textContent = '<?php echo $item ?>';
                ingredientList.appendChild(li);
                <?php
            } ?>
        }
    </script>
    
    <div id="parent">

        <!-- Rest of page -->
        <div id="page">
        
        <form id="newRecipeForm" action="" method="post" enctype="multipart/form-data">
            <div id="titleContiner"><h1 class="message" id="title">Edit/Delete Recipe</h1></div>
            <div id="pageContents">

                <div id="topParent">
                    <div id="left">

                        <div class='hprompt' id="nameContainer">
                            <p class="message" id="namePrompt">Name:</p>
                            <input class="inputs" id="recipe-name" type="text" name="recipe-name" value="<?php echo $recipe_data['name']; ?>">
                        </div>

                        <div class='hprompt' id="picContainer">
                            <p class="message" id="picPrompt">Picture:</p>
                            <input type="file" class="inputs" id="recipe-pic" name="recipe-pic" accept="image/png, image/jpeg"> 
                            <input type="hidden" id="blob" name="old-pic" value=<?php echo base64_encode($recipe_data['picture']) ?> >
                            <p class="message" id="picSpec">Must be JPG or PNG, and < 65KB</p>
                        </div>

                        <div class='hprompt' id="diffContainer">
                            <p class="message" id="diffPrompt">Difficulty:</p>
                            <button type="button" class="difficulty" onclick="difficulty_button(1)"> <img id="star-1"  src="FrontEnd/imgs/gray_star.png" alt="gray star" /> </button>
                            <button type="button" class="difficulty" onclick="difficulty_button(2)"> <img id="star-2"  src="FrontEnd/imgs/gray_star.png" alt="gray star" /> </button>
                            <button type="button" class="difficulty" onclick="difficulty_button(3)"> <img id="star-3"  src="FrontEnd/imgs/gray_star.png" alt="gray star" /> </button>
                            <button type="button" class="difficulty" onclick="difficulty_button(4)"> <img id="star-4"  src="FrontEnd/imgs/gray_star.png" alt="gray star" /> </button>
                            <button type="button" class="difficulty" onclick="difficulty_button(5)"> <img id="star-5"  src="FrontEnd/imgs/gray_star.png" alt="gray star" /> </button>
                            <input type="hidden" id="difficultyInput" name="difficultyInput" value="" autocomplete="off">
                        </div>

                        <div class='hprompt' id="timeContainer">
                            <p class="message" id="timePrompt">Time:</p>
                            <div id="timeInputContainer">
                            <input class="inputs" id="hoursInput" type="number" name="hoursInput" min="0" max="23" placeholder="Hours" value="<?php echo $recipe_data['hours']; ?>" onfocus="clearInput(this)" autocomplete="on">
                            <p class="message" id="timePrompt">:</p>
                            <input class="inputs" id="minutesInput" type="number" name="minutesInput" min="0" max="59" placeholder="Minutes" value="<?php echo $recipe_data['minutes']; ?>" onfocus="clearInput(this)" autocomplete="on">
                            </div>
                            <button type="button" class="button" onclick="setDuration()"> <img class="image" id="+Img" src="FrontEnd/imgs/+.png" alt="+"> </button>
                            <br>
                        </div>

                        <div class='hprompt' id="calorieContainer">
                            <p class="message" id="caloriePrompt">Calories:</p>
                            <input class="inputs" id="recipe-calories" type="number" name="recipe-calories" min="0" max="99999">
                        </div>

                    </div>
                    <div id="right">
                        <div class="bigButtonContainer"><button id="submitButton" class="button" type="submit"> <img class="image" id="submitImg" src="FrontEnd/imgs/confirm.png" alt="Confirm"> </button></div>
                        <div class="bigButtonContainer"><button id="cancelButton" class="button" type="button" onclick="window.location.href = '<?php echo $profile_page_link; ?>';"> <img class="image" id="cancelImg" src="FrontEnd/imgs/cancel.png" alt="Cancel"> </button></div>
                        <!-- <div class="bigButtonContainer"><button id="deleteButton" class="button" onclick="delete_recipe()"> <img class="image" id="deleteImg" src="FrontEnd/imgs/delete.png" alt="Delete"> </button></div> -->
                        <div class="bigButtonContainer"><button id="deleteButton" class="button" onclick="delete_recipe(<?php echo $recipe_id; ?>)"> <img class="image" id="deleteImg" src="FrontEnd/imgs/delete.png" alt="Delete"> </button></div>
                        
                    </div>
                </div>


                    <div class='fullprompt' id="ingredientsContainer">
                        <p class="message" id="ingredientsPrompt">Ingredients:</p>
                        <!-- container to display ingredients -->
                        <div id="ingredientList">
                            <ul id="ingredientUl"></ul>
                        </div>
                        
                        <div id="ingredientControl">
                        <input type="text" id="ingredientInput" placeholder="Enter ingredient...">
                        
                        <div id="ingButtons">
                        <div class="bigButtonContainer"><button type="button" class="button" class="ingButton" onclick="addIngredient()"> <img class="image" id="setImg" src="FrontEnd/imgs/set.png" alt="Set"> </button></div>
                        <div class="bigButtonContainer"><button type="button" class="button" class="ingButton" onclick="removeLastIngredient()"> <img class="image" id="removeImg" src="FrontEnd/imgs/remove.png" alt="Remove"> </button></div>
                        </div>
                        </div>
                        <input type="hidden" name="ingredients" id="ingredientListInput" value="">

                    </div>

                    <div class='fullprompt' id="descriptionContainer">
                        <p class="message" id="descriptionPrompt">Description:</p>
                        <textarea class="fullInputs" class="inputs" id="description" type="text" name="description" placeholder="Click to add a description..."> <?php echo $recipe_data['description']; ?> </textarea>
                    </div>

                    <div class='fullprompt' id="instructionContainer">
                        <p class="message" id="instructionPrompt">Instruction:</p>
                        <textarea class="fullInputs" class="inputs" id="instructions" type="text" name="instructions" placeholder="Click to add instructions..."> <?php echo $recipe_data['instructions']; ?> </textarea>
                    </div>
            </div>

        </form>



        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Check for newline on Shift + Enter press in description field
                document.getElementById('description').addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent newline in textarea

                        // Get current cursor position
                        var cursorPos = this.selectionStart;

                        // Insert newline character at cursor position
                        var text = this.value;
                        this.value = text.slice(0, cursorPos) + '\n' + text.slice(cursorPos);

                        // Move cursor to the next line
                        this.setSelectionRange(cursorPos + 1, cursorPos + 1);
                    }
                });

            // Check for newline on Shift + Enter press in instructions field
           document.getElementById('instructions').addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault(); // Prevent newline in textarea

                        // Get current cursor position
                        var cursorPos = this.selectionStart;

                        // Insert newline character at cursor position
                        var text = this.value;
                        this.value = text.slice(0, cursorPos) + '\n' + text.slice(cursorPos);

                        // Move cursor to the next line
                        this.setSelectionRange(cursorPos + 1, cursorPos + 1);
                    }
            }); 

            document.getElementById('newRecipeForm').addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent the default form submission

                    // check if the 

                    // format the time properly
                    setDuration()
                    // Extracting list items from the unordered list
                    var listItems = Array.from(document.querySelectorAll('#ingredientUl li')).map(li => li.textContent);

                    // Adding the list items as hidden inputs to the form
                    listItems.forEach(function(item, index) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'listItem_' + index; // Assign unique names for each item
                        input.value = item;
                        document.getElementById('newRecipeForm').appendChild(input);
                    });

                    // Additional error handling logic
                    var errorMessage = validateForm(); // Assuming validateForm() returns an error message if validation fails
                    if (errorMessage) {
                        togglePopup(errorMessage);
                        //alert(errorMessage); // Display error message
                        return; // Exit without submitting the form
                    }

                    //alert("Recipe Successfully Edited.");

                    // Now submit the form
                    //this.submit();
                    //window.location.href = "../Profile/Profile.php";
                    togglePopupS();
            });
                        
            // sanitizes duartion input (i only want positive ints, no decimal or non-numeric symbols)
            document.getElementById('hoursInput').addEventListener('input', function(event) {
                // Get the input value
                var inputValue = event.target.value;

                // Remove any non-numeric characters
                var sanitizedValue = inputValue.replace(/\D/g, '');

                // Update the input value with the sanitized value
                event.target.value = sanitizedValue;
            });

            document.getElementById('minutesInput').addEventListener('input', function(event) {
                // Get the input value
                var inputValue = event.target.value;

                // Remove any non-numeric characters
                var sanitizedValue = inputValue.replace(/\D/g, '');

                // Update the input value with the sanitized value
                event.target.value = sanitizedValue;
            });

            document.getElementById('minutesInput').addEventListener('input', function(event) {
                // Get the input value
                var inputValue = event.target.value;

                // Check if the input is a valid integer
                if (!Number.isInteger(Number(inputValue)) || Number(inputValue) < 0) {
                    // If not a valid integer or negative, set the value to empty
                    event.target.value = '';
                }
            });

        });
        </script>



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

                <p id="success"> Recipe Edited! </p>
                <br>

                <button class="btn-close-popup" onclick="document.getElementById('newRecipeForm').submit();"> 
                    Close
                </button>
            </div> 
        </div> 

        
        <!-- Popup on SUCCESS -->
        <div id="popupOverlayD" class="overlay-containerD"> 
            <div class="popup-box"> 
                <h3 style="color: #FBAE3B;">SUCCESS</h3> 

                <p id="deleted"> Recipe Deleted! </p>
                <br>

                <button class="btn-close-popup" onclick="window.location.href = '../Profile/Profile.php';"> 
                    Close
                </button>
            </div> 
        </div> 
    
    </div>
</body>
</html>
