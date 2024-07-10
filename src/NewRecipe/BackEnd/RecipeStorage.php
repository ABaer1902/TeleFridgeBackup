<?php

function getUsernameFromCookie() {

    // Check if the "username" cookie is set
    if(isset($_COOKIE['username'])) {
        // If the cookie is set, extract the username and return it
        return $_COOKIE['username'];
    } else {
        // If the cookie is not set, return null or handle the case accordingly
        return null;
    }
}

// Function to get the user ID from the cookie value
function getUserIdFromCookie($cookieValue) {

    /// Connect to the database
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute statement to retrieve user ID based on cookie value
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE cookie = ?");
    $stmt->bind_param("s", $cookieValue);
    $stmt->execute();
    $stmt->bind_result($userId);
    $stmt->fetch();

    // Close statement and database connection
    $stmt->close();
    $conn->close();

    return $userId;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $err = "";
    // var_dump($_POST);

    // see if this is a user that has a cookie
    $usercookie = getUsernameFromCookie();
    
    if($usercookie == null && $err == "") {
        $err = "Error: Outdated credentials. Please log in again.";
    } 

    // get user id
    $userId = getUserIdFromCookie($usercookie);

    if ($userId == null  && $err == "") {
        $err = "Error: No user ID found.";
    }

    
    // Retrieve text inputs from POST request and escape them
    $recipe = htmlspecialchars($_POST['recipe-name']);
    $picture = file_get_contents($_FILES['recipe-pic']['tmp_name']);
    $difficulty = htmlspecialchars($_POST['difficultyInput']);
    $hours = htmlspecialchars($_POST['hoursInput']);
    $minutes = htmlspecialchars($_POST['minutesInput']);
    $calories = htmlspecialchars($_POST['recipe-calories']);
    $description = htmlspecialchars($_POST['description']);
    $instructions = htmlspecialchars($_POST['instructions']);
    $ingredientsList = htmlspecialchars($_POST['ingredients']); // comma separated list as string

    // Parse the comma-separated list of ingredients
    $ingredients = []; // list of string (may be helpful for db and organizing recipes)
    if (isset($_POST['ingredients'])) {
        $ingredients = explode(',', $_POST['ingredients']); // explode splits a string by a string. like split() in python
        // Trim each ingredient
        $ingredients = array_map('trim', $ingredients); // trims all entries in the list
    }

    // Process the list items (this is where we need to figure out storing ingredients given a specific recipe)
    // echo "Ingredients: ";
    foreach ($ingredients as $ingredient) {
        // Output each ingredient as a list item
        // echo $ingredient . " ";
    }
    // echo "\n";

    if ($err != "") {
        echo $err;
        return;
    }




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

    // Prepare SQL statement to insert recipe data
    $sql = "INSERT INTO recipes (name, difficulty, hours, minutes, description, instructions, ingredients, creator_id, picture, calories) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siiisssisi', $recipe, $difficulty, $hours, $minutes, $description, $instructions, $ingredientsList, $userId, $picture, $calories);

    // Execute statement
    if (!$stmt->execute()) {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }




    // Close connection
    $conn->close();

}

?>