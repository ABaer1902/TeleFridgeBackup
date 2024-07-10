<?php

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

$sql;

// Retrieve the value of sortByFridge from the main php file
$sortByFridge = isset($sortByFridge) ? $sortByFridge : false;
$id = get_user_id(); 

// Use $sortByFridge to determine sorting method
if ($sortByFridge && $id != NULL) {

    // check if the users fridge lists is null
    $sql = "SELECT fridge FROM accounts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch results (array of non-empty ingredients)
    $fridgeData = array();
    while ($row = $result->fetch_assoc()) {
        // Remove leading and trailing commas, then split into an array
        $ingredients = explode(',', trim($row['fridge'], ','));

        // Process each ingredient
        foreach ($ingredients as $ingredient) {
            if (!empty($ingredient)) {
                // Add non-empty ingredients to the csvData array
                $fridgeData[] = $ingredient;
                // echo $ingredient . "\n"; 
            } else {
                // echo "empty ingredient\n"; 
            }
        }
    }

    // cheks for empty fridge
    if (empty($fridgeData)) {
        echo "Your fridge is empty, add ingredients to it to enjoy the sort by fridge feature.";
        return;
    }

    // link info for most recent recipes (RESTRICTIONS)
    $sql = "SELECT a.username AS creator_username, a.id AS creator_id, r.name AS recipe_name, r.id AS recipe_id, r.picture AS recipe_pic, r.date_created, r.difficulty, r.likes, r.dislikes
    FROM recipes AS r 
    INNER JOIN accounts AS a ON r.creator_id = a.id 
    ORDER BY r.date_created DESC";
    $result = $conn->query($sql);

    echo "<ol>";
    if ($result->num_rows > 0) {
        $diffView = array("","FrontEnd/imgs/1star.png","FrontEnd/imgs/2star.png","FrontEnd/imgs/3star.png","FrontEnd/imgs/4star.png","FrontEnd/imgs/5star.png");
        while($row = $result->fetch_assoc()) {

            // Chop up recipe ingredients
            $sql2 = "SELECT ingredients FROM recipes WHERE id = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $row['recipe_id']);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            // Fetch results (array of non-empty ingredients)
            $ingredientData = array();
            while ($row2 = $result2->fetch_assoc()) { 
                // Remove leading and trailing commas, then split into an array
                $ingredients = explode(',', trim($row2['ingredients'], ','));

                // Process each ingredient
                foreach ($ingredients as $ingredient) {
                    if (!empty($ingredient)) {
                        // Add non-empty ingredients to the ingredientData array
                        $ingredientData[] = $ingredient;
                        // echo $ingredient . "\n";
                    } else {
                        // echo "empty ingredient\n";
                    }
                }
            }

            // Check if the recipe is fully in the fridge
            $allIngredientsAvailable = true;

            foreach ($ingredientData as $ingredient) {
                if (!in_array($ingredient, $fridgeData)) {
                    // If an ingredient is not in fridgeData, then set the flag to false
                    $allIngredientsAvailable = false;
                    break;  // No need to check further, one missing ingredient is enough
                }
            }

            if ($allIngredientsAvailable == true) {
                echo "<li>";
                echo "<a class='recipeLink' href='../Recipe/Recipe.php?recipe_id={$row['recipe_id']}'>";
                echo "<div class='recipe'>";
                echo "<input type='hidden' class='recipe-id' value='{$row['recipe_id']}'>";
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

                echo "<div class='right'>";
                echo "<div class='recipeLikes' style='display:inline-flex;'><img src='FrontEnd/imgs/heart_filled.png' alt='Likes: '> <p class='recipeMsg'>{$row['likes']}</p></div>";
                echo "<img src='FrontEnd/imgs/{$row['difficulty']}star.png' alt='{$row['difficulty']}'>";
                echo "<div class='recipeDislikes' style='display:inline-flex;'><img src='FrontEnd/imgs/broken_heart_filled.png' alt='Dislikes: '> <p class='recipeMsg'>{$row['dislikes']}</p></div>";
                echo "</div>";

                echo "</div>";
                echo "</a>";
                echo "</li>";
            }
            
        }
    }
    echo "</ol>";


} else {

    // link info for most recent recipes (NO RESTRICTIONS)

    $sql = "SELECT a.username AS creator_username, a.id AS creator_id, r.name AS recipe_name, r.id AS recipe_id, r.picture AS recipe_pic, r.date_created, r.difficulty, r.calories, r.likes, r.dislikes
            FROM recipes AS r 
            INNER JOIN accounts AS a ON r.creator_id = a.id 
            ORDER BY r.date_created DESC 
            LIMIT 10";
    $result = $conn->query($sql);
    $recipedata = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'relevance';

    // sort calories
    if ($sort == "calories low to high"){
        usort($recipedata, "calories_low_to_high");
    }
    else if ($sort == "calories high to low"){
        usort($recipedata, "calories_high_to_low");
    }

    echo "<ol>";
    // Step 3: Fetch and display data
    if ($result->num_rows > 0) {
        $diffView = array("","FrontEnd/imgs/1star.png","FrontEnd/imgs/2star.png","FrontEnd/imgs/3star.png","FrontEnd/imgs/4star.png","FrontEnd/imgs/5star.png");
        $i = 0;
        while($i < count($recipedata)) {
            $row = $recipedata[$i];
            echo "<li>";
            echo "<a class='recipeLink' href='../Recipe/Recipe.php?recipe_id={$row['recipe_id']}'>";
            echo "<div class='recipe'>";
            echo "<input type='hidden' class='recipe-id' value='{$row['recipe_id']}'>";
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
    } else {
        echo "No recipes found.";
    }
    echo "</ol>";
}

?>