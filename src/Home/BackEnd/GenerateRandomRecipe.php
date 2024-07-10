<?php
include '../../GeneralBackEnd/ServerChecks.php';

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

// Query to select a random recipe ID from the recipes table
$sql = "SELECT id, creator_id FROM recipes ORDER BY RAND() LIMIT 1";

// Execute the query
$result = $conn->query($sql);

// Check if query was successful and if any rows were returned
if ($result && $result->num_rows > 0) {
    // Fetch the row and extract the recipe ID
    $row = $result->fetch_assoc();
    $recipeId = $row['id'];
    $creatorId = $row['creator_id'];

    // Return the random recipe ID as JSON response
    $response = array('recipe_id' => $recipeId, 'creator_id' => $creatorId);
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Handle the case where no rows were returned (e.g., no recipes in the database)
    $errorResponse = array('error' => 'No recipes found in the database');
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
}

$conn->close();
?>
