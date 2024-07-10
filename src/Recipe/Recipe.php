<html>
    <head>
        <title>Recipe</title>
        <link rel="stylesheet" href="FrontEnd/style.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>

        <?php
            include '../GeneralBackEnd/ServerChecks.php';

            // doesnt check logged in bc guests should be able to view recipes
            $user_id = get_user_id();

            session_start();

            $creds = get_credentials();
            $serverName = $creds["serverName"];
            $uid = $creds["uid"];
            $pass = $creds["pass"];
            $database = $creds["database"];

            // establish a connection
            $conn = new mysqli($serverName, $uid, $pass, $database);

            // check if connection was successful
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // get recipe_id
            if (isset($_GET["recipe_id"])){
                $recipe_id = $_GET["recipe_id"];
                $_SESSION['recipe_id'] = $_GET["recipe_id"];
            }

            // get the recipe data from database
            $sql = "SELECT * FROM recipes WHERE `id`='" . $recipe_id . "'";

            // get result from database
            $result = mysqli_query($conn, $sql);

            // parse result into usable data
            $recipedata = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];

            mysqli_free_result($result);

            // get the user who posted the recipe from the db
            $creator_id = $recipedata["creator_id"];
            $sql = "SELECT * FROM accounts WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $creator_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $creatordata = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
            mysqli_free_result($result);
            
            // Format image to be displayed
            $profile_pic = "FrontEnd/imgs/default_pic.png";
            if ($creatordata["profile_picture"] != "") {
                $profile_pic = 'data:image/jpg;base64,'.base64_encode($creatordata["profile_picture"]);
            }

            $sql = "SELECT reaction, difficulty FROM likes_dislikes WHERE recipe_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $recipe_id, $user_id);
            $stmt->execute();

            $result = $stmt->get_result();

            $usermetrics = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if (empty($usermetrics)) {
                // echo 'user metrics returned nothing';
                $usermetrics = array("reaction" => 0, "difficulty" => 0);
            }
            else {
                $usermetrics = $usermetrics[0];
                // echo 'user ereaction ' . $usermetrics['reaction'];
            }
            mysqli_free_result($result);

            // set time
            $recipedata["time"] = "";
            if ($recipedata["hours"] == 0 && $recipedata["minutes"] == 0){
                $recipedata["time"] = "Very Quick";
            }
            else if ($recipedata["hours"] != 0 && $recipedata["minutes"] == 0){
                $recipedata["time"] = strval($recipedata["hours"]) . " Hours";
            }
            else if ($recipedata["hours"] == 0 && $recipedata["minutes"] != 0){
                $recipedata["time"] = strval($recipedata["minutes"]) . " Minutes";
            }
            else {
                $recipedata["time"] = strval($recipedata["hours"]) . " Hours, " . strval($recipedata["minutes"]) . " Minutes";
            }
            
            // create creator link
            $creatordata["user_profile_link"] = "../Profile/Profile.php?profile_id={$creator_id}";

            // SBARINA'S SAVE LOGIC ---------------------------------------------------------------
            $isSaved = false;
            $userID = get_user_id();

            // Check if the recipe is saved by the current user
            $sql = "SELECT * FROM saves WHERE user_id = ? AND recipe_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $userID, $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // If a row is returned, it means the recipe is saved by the user
            if ($result->num_rows > 0) {
                $isSaved = true;
            }

            // Get the comment data
            $sql = "SELECT * FROM comments WHERE recipe_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $recipe_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $commentdata = mysqli_fetch_all($result, MYSQLI_ASSOC);

            foreach ($commentdata as $i => $data) {
                // Get profile name
                $profile_id = $data["user_id"];
                $sql = "SELECT username FROM accounts WHERE id=$profile_id";
                $result = mysqli_query($conn, $sql);
                $profilename = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]["username"];
                mysqli_free_result($result);
                // embed profile name with link
                $profile_url = "../Profile/Profile.php?profile_id=$profile_id";
                $embedded_profilename = "<a href='$profile_url'>" . $profilename . "</a>";
                $commentdata[$i]["profilename"] = $embedded_profilename;
            }
        
            $conn->close();
            $meal_flag = false;
            
        ?>

        <script>
            var likes = <?php echo $recipedata['likes']; ?>;
            var dislikes = <?php echo $recipedata['dislikes']; ?>;
        </script>
        
        
        <div class="header">

            <div class="left-header">
                <button id="back-button" onclick="backbtn(<?php $meal_flag ?>);"> <img src="FrontEnd/imgs/back_arrow.png" alt="<---" /> </button>

                <?php echo " <h1 id='recipeName'>{$recipedata['name']}</h1>"; ?>
                <div class="profile-data">
                    <img src=<?php echo $profile_pic ?> alt="Profile_pic" width="75" height="75" class="profile-image">
                    <div class="username"> <a id="profileLink" class="message" href=<?php echo $creatordata['user_profile_link']; ?>> <?php echo $creatordata['username']; ?> </a> </div>
                </div>
            </div>

            <div class="right-header">
                <div class="inner-right-header">
                    <div class="row">
                        <?php $picture = "../imgs/defaultRecipePic.png";
                            if ($recipedata['picture'] != '') {
                                $pic = base64_encode($recipedata['picture']);
                                $picture = "'data:image/jpeg;base64, $pic'";
                            }   
                            echo "<img class='pic' src={$picture} >";
                        ?>
                    </div>
                    <div class="row">
                        <div class="message" id="like-count"> <?php echo $recipedata["likes"] ?> </div>
                        <button id="like-button"> <img id="like-picture" src=<?php if ($usermetrics["reaction"] == 1){ echo "FrontEnd/imgs/heart_filled.png"; } else { echo "FrontEnd/imgs/heart_outline.png"; } ?> alt="like" /> </button>
                        <button id="dislike-button"> <img id="dislike-picture" src=<?php if ($usermetrics["reaction"] == -1){ echo "FrontEnd/imgs/broken_heart_filled.png"; } else { echo "FrontEnd/imgs/broken_heart_outline.png"; } ?> alt="dislike" /> </button>
                        <div class="message" id="dislike-count"> <?php echo $recipedata["dislikes"] ?> </div>
                        <button id="save-button"> <img id="saved-picture" src=<?php if ($isSaved == true){ echo "FrontEnd/imgs/saved.png"; } else { echo "../imgs/navbar/saves.png"; } ?> alt="save" /> 
                            <?php
                            // Add hidden inputs for user_id and recipe_id
                            $userID = get_user_id();
                            $recipe_id = $_GET['recipe_id'];
                            echo '<input type="hidden" name="user_id" value="' . $userID . '">';
                            echo '<input type="hidden" name="recipe_id" value="' . $recipe_id . '">';
                            ?>    
                        </button>

                        <button id="meal-button" style="cursor:pointer;"> <img id="meal-picture" style="width: 30px;" src="FrontEnd/imgs/plus.png" alt="meal" /> </button>

                        
                    </div>
                    <div class="row">
                        <div id="difficultyMsg" class="message">Difficulty: </div>
                        <img id="star-1"  src=<?php if ($recipedata["difficulty"] >= 1) { echo "FrontEnd/imgs/yellow_star.png"; } else { echo "FrontEnd/imgs/gray_star.png"; } ?> alt=<?php if ($recipedata["difficulty"] >= 1) { echo "Yellow Star"; } else { echo "Gray Star"; } ?>/> 
                        <img id="star-2"  src=<?php if ($recipedata["difficulty"] >= 2) { echo "FrontEnd/imgs/yellow_star.png"; } else { echo "FrontEnd/imgs/gray_star.png"; } ?> alt=<?php if ($recipedata["difficulty"] >= 2) { echo "Yellow Star"; } else { echo "Gray Star"; } ?>/>
                        <img id="star-3"  src=<?php if ($recipedata["difficulty"] >= 3) { echo "FrontEnd/imgs/yellow_star.png"; } else { echo "FrontEnd/imgs/gray_star.png"; } ?> alt=<?php if ($recipedata["difficulty"] >= 3) { echo "Yellow Star"; } else { echo "Gray Star"; } ?>/>
                        <img id="star-4"  src=<?php if ($recipedata["difficulty"] >= 4) { echo "FrontEnd/imgs/yellow_star.png"; } else { echo "FrontEnd/imgs/gray_star.png"; } ?> alt=<?php if ($recipedata["difficulty"] >= 4) { echo "Yellow Star"; } else { echo "Gray Star"; } ?>/> 
                        <img id="star-5"  src=<?php if ($recipedata["difficulty"] == 5) { echo "FrontEnd/imgs/yellow_star.png"; } else { echo "FrontEnd/imgs/gray_star.png"; } ?> alt=<?php if ($recipedata["difficulty"] >= 5) { echo "Yellow Star"; } else { echo "Gray Star"; } ?>/>
                    </div>
                    <div class="row">
                        <div class="message">Calories: <?php echo $recipedata['calories']; ?></div>
                    </div>
                    <div class="row">
                        <div class="message">Time: <?php echo $recipedata['time']; ?></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="centered-box">
            <p class="content-message">
                Description:
                </br>
                <?php echo $recipedata['description']; ?>
                </br>
                </br>
                Ingredients:
                </br>
                <?php echo $recipedata['ingredients']; ?>
                </br>
                </br>
                Instructions:
                </br>
                <?php echo nl2br($recipedata['instructions']); ?>
                </br>
                </br>
                </br>
                </br>
                </br>
                </br>
                Comments:
                </br>
                </br>
                <button onclick="add_comment();" class="comment_button">Add Comment:</button>
                <input class="comment_bar" id="comment_input" type="text" placeholder="Enter Your Comment Here" maxlength="120">
                </br>
                </br>
                <?php 
                    foreach ($commentdata as $i => $data) {
                        echo "<span class='profileFont'>";  
                        echo $data["profilename"];
                        echo "</span>";
                        echo ": " . $data["content"];
                        echo "</br>";
                        echo "</br>";
                    }
                ?>
            </p>
        </div>

        <!-- Popup for meal plan -->
        <div id="popupOverlay" class="overlay-container"> 
            <div class="popup-box"> 
                <h2 style="color: #FBAE3B;">Add to Meal Plan?</h2> 
                <form id="mealplan" class="form-container" method="POST">
                    <br>
                    <label class="form-label" 
                        for="day">      
                        
                    Pick a day:
                    <br>
                    </label> 
                    <select id="days" name="day">
                        <option value="sunday">Sunday</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                    </select>
                    <br>
                    <br>
                    <input type="hidden" name="user_id" value="<?php echo $user_id ?>">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe_id ?>">
                    <button id="submit-button" class="btn-submit" type="submit">Submit</button> 
                   
                </form> 
                <button class="btn-close-popup" onclick="togglePopup()"> 
                Close 
                </button>

            </div> 
        </div>  


        <script src="FrontEnd/App.js"></script>
    </body>
</html>
