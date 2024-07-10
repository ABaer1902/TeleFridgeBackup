<?php
include '../../GeneralBackEnd/ServerChecks.php';

// Connect to the database
$creds = get_credentials();
$serverName = $creds["serverName"];
$uid = $creds["uid"];
$pass = $creds["pass"];
$database = $creds["database"];

// Get user id
$user_id = get_user_id();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check that the user id matches the post request
    $creator_id = htmlspecialchars($_POST["creator_id"]);
    if ($creator_id != $user_id) {
        return;
    }

    // Get the desired collection and recipe id
    $collection_id = htmlspecialchars($_POST["collection_id"]);
    $recipe_id = htmlspecialchars($_POST["recipe_id"]);

    // Establish a database connection
    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the recipe already exists in the collection
    $sql = "INSERT INTO collection_recipes (collection_id, recipe_id)
            SELECT ?, ?
            FROM dual
            WHERE NOT EXISTS (
                SELECT 1
                FROM collection_recipes
                WHERE collection_id = ? AND recipe_id = ?
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $collection_id, $recipe_id, $collection_id, $recipe_id);
    $stmt->execute();

    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>
