<?php 
include '../../GeneralBackEnd/ServerChecks.php';

// check that the user is logged in
checkLoggedIn();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // get recipe data
    $user_id = get_user_id();
    $recipe_id = htmlspecialchars($_POST["recipe_id"]);
    echo $user_id;
    echo $recipe_id;

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

    // check if they have already saved this recipe
    $sql = "SELECT id FROM saves WHERE user_id=? AND recipe_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $user_id, $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any row is returned
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();

        // Remove the row from saves table
        $sql_delete_saves = "DELETE FROM saves WHERE user_id = ? AND recipe_id = ?";
        $stmt_delete_saves = $conn->prepare($sql_delete_saves);
        $stmt_delete_saves->bind_param('ii', $user_id, $recipe_id);
        $stmt_delete_saves->execute();

        // Check if the row is successfully deleted from saves table
        if ($stmt_delete_saves->affected_rows > 0) {
            // Remove from collection_recipes table
            $sql_delete_collection_recipes = "DELETE cr FROM collection_recipes AS cr INNER JOIN collections AS c ON cr.collection_id = c.id WHERE c.creator_id = ? AND cr.recipe_id = ?";
            $stmt_delete_collection_recipes = $conn->prepare($sql_delete_collection_recipes);
            $stmt_delete_collection_recipes->bind_param('ii', $user_id, $recipe_id);
            $stmt_delete_collection_recipes->execute();

            // Check if the row is successfully deleted from collection_recipes table
            if ($stmt_delete_collection_recipes->affected_rows > 0) {
                // Additional actions if needed
            }
        }
    } else {
        // get creator_id 
        $sql = "SELECT creator_id FROM recipes WHERE id=$recipe_id";
        $result = mysqli_query($conn, $sql);
        $creator_id = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]["creator_id"];
        mysqli_free_result($result);

        // add to saves
        $sql = "INSERT INTO saves (user_id, recipe_id, creator_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $user_id, $recipe_id, $creator_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

}
?>