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

$sql = "SELECT r.id AS recipe_id, r.name AS recipe_name, r.difficulty, r.hours, r.minutes, r.date_created, r.dislikes, r.likes, accounts.username AS creator_username, accounts.id AS creator_id
FROM collection_recipes
INNER JOIN recipes AS r ON collection_recipes.recipe_id = r.id
INNER JOIN accounts ON r.creator_id = accounts.id
WHERE collection_recipes.collection_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $collection_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<input type='hidden' class='collection-id' value='" . $collection_id . "'>";

?>

<ol id="savesList">
    <?php
    // Step 3: Fetch and display data
    if ($result->num_rows > 0) {
        $diffView = array("","FrontEnd/imgs/1star.png","FrontEnd/imgs/2star.png","FrontEnd/imgs/3star.png","FrontEnd/imgs/4star.png","FrontEnd/imgs/5star.png");
        while($row = $result->fetch_assoc()) {
            echo "<li class='recipe' style='list-style-type: none;'>";
            echo "<a class='recipeLink' href='../Recipe/Recipe.php?recipe_id={$row['recipe_id']}'>";
            echo "<div class='recipeContent'>";
            echo "<input type='hidden' class='recipe-id' value='{$row['recipe_id']}'>";
            echo "<input type='hidden' class='user-id' value='{$row['creator_id']}'>"; 
            echo "<input type='hidden' class='recipe-name' value='{$row['recipe_name']}'>";
            echo "<input type='hidden' class='creator_id' value='{$row['creator_id']}'>"; 
            echo "<input type='hidden' class='creator_username' value='{$row['creator_username']}'>"; 
            echo "<input type='hidden' class='hours' value='{$row['hours']}'>";
            echo "<input type='hidden' class='minutes' value='{$row['minutes']}'>";
            echo "<input type='hidden' class='difficulty' value='{$row['difficulty']}'>";
            echo "<input type='hidden' class='date_created' value='{$row['date_created']}'>"; 
            echo "<input type='hidden' class='likes' value='{$row['likes']}'>";
            echo "<input type='hidden' class='dislikes' value='{$row['dislikes']}'>"; 


            echo "<div class='nameR'>";
            echo "<p class='recipeMsg'>{$row['recipe_name']}</p>";
            echo "</div>";

            echo "<div class='bottomR'>";
            echo "<div class='difficulty'>";
            echo "<img src='FrontEnd/imgs/{$row['difficulty']}star.png' alt='{$row['difficulty']}'>";
            echo "</div>";

            echo "<div class='time'>";
            echo "<p>{$row['hours']} hr</p> <p>{$row['minutes']} min</p>";
            echo "</div>";
            echo "</div>";

            echo "</div>";
            echo "</a>";
            echo "</li>";
        }
    } else {
        echo "<p class='recipeMsg'>Oops... You don't have any saved in this collection</p>";
    }

    $conn->close();
    ?>
</ol>
