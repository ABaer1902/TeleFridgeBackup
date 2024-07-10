<?php
    include '../GeneralBackEnd/ServerChecks.php';

    // handles fridge filter
    $sortByFridge = True;
    if (!isset($_GET['sortByFridge'])) {
        $sortByFridge = false;
    } else if (isset($_GET['sortByFridge']) && $_GET['sortByFridge'] === 'false') {
        $sortByFridge = false;
    }

    session_start();
    // INFORMATION:
    // SEARCHING OR USING SORT/FILTER WILL UPDATE THE QUERY STRING AND REFRESH THE PAGE
    // QUERY STRING WILL PARSE UPON PAGE ENTRY AND SAVE THE VALUES
    // SEARCH RESULTS TAB WILL ONLY OPEN IF 'SEARCH' IS IN QUERY STRING

    //starting function: parses query string and returns variable defaults
    function loadQuery($sortByFridge) {
        $vars = [];
        // structure:
        // search=? sort=? diet=? ingredients=?,?,?...
        // Hooray for ternary operators :)
        $vars['search'] = isset($_GET['search']) ? substr($_GET['search'], 1, -1) : NULL;
        $vars['sort'] = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';
        $vars['diet'] = isset($_GET['diet']) ? $_GET['diet'] : NULL;
        $vars['ingredients'] = isset($_GET['ingredients']) ? str_getcsv($_GET['ingredients']) : [];
        if ($sortByFridge) {
            $vars['ingredients'] = array_merge(str_getcsv(retrieveFridge($_COOKIE['username'])), $vars['ingredients']);
        }
        return $vars;
    }

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

    function calories_low_to_high($a, $b) {
        return $a["calories"] - $b["calories"];
    }
    
    function calories_high_to_low($a, $b) {
        return  $b["calories"] - $a["calories"];
    }

    // process and return post sort/filter recipes if called (only called if it is in query string)
    function loadSearch($vars) {
        $search = $vars['search'];
        $sort = $vars['sort'];
        $diet = $vars['diet']; // will ignore for now because it's just a filter of certain ingredients'
        $ingredients = $vars['ingredients']; //parse from ingredients tab (csv)

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

        //search (currently on KEYWORD mode)
        //algo splits by space char and ranks by keywords present in name
        $search_array = explode(' ', $search);
        $search_query = "SELECT a.username AS creator_username, a.id AS creator_id, r.name AS recipe_name, r.picture AS recipe_pic, r.id AS recipe_id, r.date_created, r.difficulty, r.calories, 
                         r.ingredients, r.likes, r.dislikes, (r.likes - COALESCE(r.dislikes, 0)) AS net_likes
                         FROM recipes AS r 
                         INNER JOIN accounts AS a ON r.creator_id = a.id WHERE (";
        //$search_query = "SELECT * FROM recipes WHERE (";
        $keyword_count = sizeof($search_array);
        //search keywords
        for ($i = 0; $i < $keyword_count; $i++) {
            $search_query .= "(r.name LIKE '%" . $search_array[$i] . "%')";
            if ($i != $keyword_count-1)
                $search_query .= " OR ";
        }
        $search_query .= ') ';
        //ingredients
        $num_ingredients = sizeof($ingredients);
        if ($num_ingredients > 0) {
            //(AND mode)
            /*
            for ($i = 0; $i < $num_ingredients; $i++) {
                $search_query .= "AND ingredients LIKE '%" . $ingredients[$i] . "%' ";
            }
            */
            //(OR mode)
            $search_query .= "AND (";
            for ($i = 0; $i < $num_ingredients; $i++) {
                if ($ingredients[$i] != "") {
                    $search_query .= "r.ingredients LIKE '%" . $ingredients[$i] . "%' ";
                    if ($i != $num_ingredients-1)
                        $search_query .= " OR ";
                }
            }
            $search_query .= ') ';
        }
        //order by keyword
        $search_query .= "ORDER BY ";
        for ($i = 0; $i < $keyword_count; $i++) {
            $search_query .= "(r.name LIKE '%" . $search_array[$i] . "%')";
            if ($i != $keyword_count-1)
                $search_query .= " + ";
        }
        $search_query .= "DESC";
        //$search_query = "SELECT * FROM recipes WHERE name LIKE '%$search%'";
        
        //sort filters
        if ($sort == "newest") {
            $search_query .= ", r.id DESC";
        }
        if ($sort == "popular") {
            $search_query .= ", net_likes DESC";
        }
        if ($sort == "easy") {
            $search_query .= ", r.difficulty ASC";
        }
        if ($sort == "quick") {
            $search_query .= ", hours*60 + minutes ASC";
        }

        //limit X=8
        $search_query .= " LIMIT 8";

        //$stmt = $conn->prepare($search_query);
        //$stmt->bind_param("ss", $column, $search);
        $searchResult = $conn->query($search_query);

        $conn->close();

        return $searchResult;
    }

    //basic function to redirect (or update query string url)
    function redirect($url) {
        header('Location: '.$url);
        die();
    }

    //basic function to add a query to the string, returns new url (does not redirect) (does not check for query presence)
    function query_Add($url, $key, $value) {
        if (strpos($url, '?') === false) {
            return ($url .'?'. $key .'='. $value);
        } else {
            return ($url .'&'. $key .'='. $value);
        }
    }

    //basic function to remove a query from the string, returns new url (does not redirect) (does not check for query presence)
    function query_Remove($url, $key) {
        $url = preg_replace('/(.*)(&)'. $key .'=[^&]+?(&)/i', '$1$2$4', $url .'&');
        $url = substr($url, 0, -1);
        $url = preg_replace('/(.*)(?)'. $key .'=[^&]+?&/i', '$1$2$4', $url .'&');
        $url = substr($url, 0, -1);
        return ($url);
    }

    //function to change a query string value (does not redirect) (checks for query presence)
    function changeQuerySingle($url, $key, $value) {
        $newURL = $url;
        //echo "Original: " . $url . "\n";
        if (isset($_GET[$key])) {
            $newURL = query_Remove($url, $key);
            //echo "removed: " . $newURL . "\n";
        }
        $newURL = query_Add($newURL, $key, $value);
        //echo "added: " . $newURL . "\n";
        return $newURL;
    }

    //POST from search bar, updates or adds search in query
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['search'])) {
            $url = $_POST['url'];
            $search = rawurlencode($_POST['search']);
            $url = changeQuerySingle($url, 'search', '"' . $search . '"');
            //echo "\n" . $url;
            //exit;
            redirect($url);
        }
        if (isset($_POST['all'])) {
            $url = $_POST['url'];
            if (isset($_POST['item'])){
                $ing_string = implode(",", $_POST['item']);
                $url = changeQuerySingle($url, 'ingredients', $ing_string);
                redirect ($url);
            } else {
                $url = query_Remove($url, 'ingredients');
                redirect ($url);
            }
        }
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

    <title>Tele-Fridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="FrontEnd/style.css">
    <script src="FrontEnd/App.js"></script>
    <link rel="stylesheet" href="../GeneralFrontEnd/navBar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>

</head>

<body>

    <?php
        //start function: process query, set variables, display search if present
        $vars = loadQuery($sortByFridge);
        if ($vars['search'] != NULL) {
            //loadSearch($vars);
        }
        $url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    ?>
    <div id="parent">
        <div id="page">
            <form id="search" class="search" method="post">
                <div class="hiddenContainer"><input name="url" id="url" type="hidden" value="<?php echo $url;?>"/></div>
                <div class="inputContainer"><input class="inputs" id="search" name="search" type="text" placeholder="Search..."></div>
                <button type="submit" id="submitbtn"><i class="fa fa-search"></i></button>

            </form>

            <div class="filters">
                <div class="sortBy">
                    <button onclick="sortDD()" class="dropbtn">Sort By</button>
                    <div id="sortDropdown" class="dropdown-content">

                        <a href="<?php echo changeQuerySingle($url, 'sort', 'relevance');?>">Relevance</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'newest');?>">Newest</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'popular');?>">Most Popular</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'quick');?>">Quick</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'easy');?>">Easy</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'calories low to high');?>">Calories Low to High</a>
                        <a href="<?php echo changeQuerySingle($url, 'sort', 'calories high to low');?>">Calories High to Low</a>

                    </div>
                </div>
                <div class="feelingHungry">
                    <button id="fridgeFilterButton" style="border: none;background: none;cursor:pointer;<?php if ($sortByFridge) echo " background-color: #8bff5d"?>" onclick="updateSortByFridge();" class="button">Fridge</button>
                </div>
                <div class="feelingHungry">
                    <button id="feelingHungryButton" style="border: none;background: none;cursor:pointer;">Im feeling Hungry!</button>
                </div>
                <div class="allFilters">
                    <button onclick="dropdown()" class="dropbtn">All Filters</button>
                    <div id="filtersDropdown" class="dropdown-content">
                        <form id="ingredients" class="ingredients" method="post">
                            <?php
                            $ingredients_array = ['Beef', 'Pork', 'Chicken', 'Vegetables', 'Grain', 'Fruits', 'Dairy', 'Bread', 'Cereal'];
                            foreach($ingredients_array as $ing) {
                                ?>
                                <div class="inputContainer"><input type="checkbox" name="item[]" value='<?php echo $ing; ?>' <?php if (in_array($ing, $vars['ingredients'])) echo "checked";?>><?php echo $ing; ?><br></div>
                                <?php
                            }
                            ?>
                            <div class="hiddenContainer"><input name="url" id="url" type="hidden" value="<?php echo $url;?>"/></div>
                            <div class="hiddenContainer"><input type="hidden" name="all" value='default'/></div>
                            <button type="submit">Confirm</button>
                        </form>
                    </div>

                </div>

            </div>


            <!-- NEW STUFF FROM SABRINA -->
            <script>
            function updateSortByFridge() {
                const currentUrl = new URL(window.location.href);
                const sortByFridgeValue = currentUrl.searchParams.get('sortByFridge') === 'true' ? 'false' : 'true';
                currentUrl.searchParams.set('sortByFridge', sortByFridgeValue);
                window.history.pushState({}, '', currentUrl);
                window.location.reload();
            }
            </script>

            <!--<button id="feelingHungryButton">Im feeling Hungry!</button>-->

            <div class="posts">
                <div class="onePost" style="display: <?php echo ($vars['search'] === NULL ? 'none' : 'block');?>; ">
                    <h2 class="message">Search Results <a href="<?php echo query_Remove($url, 'search');?>">[X]</a></h2>
                    <div class="recipeGroup" id="searchres">
                    <!-- Example: <div class="recipeContainer"><img id="recipePic" src="../imgs/defaultProfilePic.jpg"/><div class="recipeCaption">Recipe Name</div></div> -->
                    <?php
                    if ($vars['search'] != NULL) {
                        $searchResults = loadSearch($vars);
                        $count = $searchResults->num_rows;
                        if ($count == 0) {
                            ?>
                            <div><br><h3 class="message">No Search Results Found</h3></div>
                            <?php
                        }

                        $recipedata = mysqli_fetch_all($searchResults, MYSQLI_ASSOC);
                        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';

                        // sort calories
                        if ($sort == "calories low to high"){
                            usort($recipedata, "calories_low_to_high");
                        }
                        else if ($sort == "calories high to low"){
                            usort($recipedata, "calories_high_to_low");
    }

                        //$searchArray = $searchResults->fetch_assoc();
                        /*
                        for ($i = 0; $i < $count; $i += 1) {
                            ?>
                            <div class="recipeContainer">
                                <a href="#">
                                    <div class="recipeCaption"><a href="#"><?php echo $searchArray[$i][2]; ?></a></div>
                                </a>
                            </div>
                            <?php
                        }*/
                        $diffView = array("","FrontEnd/imgs/1star.png","FrontEnd/imgs/2star.png","FrontEnd/imgs/3star.png","FrontEnd/imgs/4star.png","FrontEnd/imgs/5star.png");
                        $i = 0;
                        while($i < count($recipedata)) {
                            $row = $recipedata[$i];
                            echo "<li style='list-style-type: none;'>";
                            echo "<a class='recipeLink' href='../Recipe/Recipe.php?recipe_id={$row['recipe_id']}'>";
                            echo "<div class='recipe'>";
                            echo "<input type='hidden' class='recipe-id' value='{$row['recipe_id']}'>";
                            //echo "<input type='hidden' class='recipe-pic' value='{$row['recipe_pic']}'>";
                            echo "<input type='hidden' class='user-id' value='{$row['creator_id']}'>"; 
                            echo "<input type='hidden' class='recipe-name' value='{$row['recipe_name']}'>";
                            echo "<input type='hidden' class='creator_username' value='{$row['creator_username']}'>"; 
                            echo "<input type='hidden' class='difficulty' value='{$row['difficulty']}'>";
                            echo "<input type='hidden' class='date_created' value='{$row['date_created']}'>"; 
                            echo "<input type='hidden' class='likes' value='{$row['likes']}'>";
                            echo "<input type='hidden' class='dislikes' value='{$row['dislikes']}'>"; 


                            echo "<div class='left'>";
                            $picture = "../imgs/defaultRecipePic.png";
                            if ($row['recipe_pic'] != '') {
                                $pic = base64_encode($row['recipe_pic']);
                                $picture = "'data:image/jpeg;base64, $pic'";
                            }   
                            echo "<img class='pic' src={$picture} >";
                            echo "<p class='recipeMsg'>{$row['recipe_name']}</p>";
                            echo "</div>";

                            echo "<div class='middle'>";
                            echo "<p class='calories'>Calories: {$row['calories']}</p>";
                            echo "</div>";

                            echo "<div class='right'>";
                            echo "<div class='recipeLikes' style='display:inline-flex;'><img src='FrontEnd/imgs/heart_filled.png' alt='Likes: '> <p class='recipeMsg'>{$row['likes']}</p></div>";
                            echo "<img src='FrontEnd/imgs/{$row['difficulty']}star.png' alt='{$row['difficulty']}'>";
                            echo "<div class='recipeDislikes' style='display:inline-flex;'><img src='FrontEnd/imgs/broken_heart_filled.png' alt='Dislikes: '> <p class='recipeMsg'>{$row['dislikes']}</p></div>";
                            echo "</div>";

                            echo "</div>";
                            echo "</a>";
                            echo "</li>";
                            $i++;
                        }
                    }
                        ?>
                    </div>
                </div>
                <?php if (isset($_COOKIE['username'])) {?>
                <div class="onePost">
                    <h2 class="message">Following</h2>
                    <div class="recipeGroup" id="following">
                    <?php include 'BackEnd/Following.php'; ?>
                    </div>
                </div>
                <?php
                    }
                ?>
                <div class="onePost">
                    <h2 class="message">Months Trending</h2>
                    <div class="recipeGroup" id="trending">
                    <?php include 'BackEnd/MonthsTrending.php'; ?>
                    </div>
                </div>

                <div class="onePost">
                    <h2 class="message">Most Popular</h2>
                    <div class="recipeGroup" id="popular">
                    <?php include 'BackEnd/MostPopular.php'; ?>
                    </div>
                </div>

                <div class="onePost">
                    <h2 class="message">Most Recent</h2>
                    <!-- id needs change -->
                    <div class="recipeGroup" id="recents"> 
                    <?php include 'BackEnd/MostRecentRecipes.php'; ?>
                    </div>
                </div>


            </div>
        
    
        </div>

        <!-- Navigation Bar -->
        <div id="navBar">
            <!-- Need to allow ability to take image from database -->
            <div id="pictureContainerNB"> <img id="profilePicNB" src= <?php echo $profile_picture; ?> > </div>

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


</body>
</html>