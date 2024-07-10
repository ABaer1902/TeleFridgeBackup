<?php
include '../../GeneralBackEnd/ServerChecks.php';
echo "<script> console.log('reaches here?'); </script>";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    session_start();

    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $day = htmlspecialchars($_POST['day']);

    $user_id = get_user_id();
    $recipe_id = $_POST['recipe_id'];
    $recipe_link = "../Recipe.php?recipe_id=$recipe_id";



    // Update username
    $sql = "INSERT INTO meal_plan (user_id, recipe_id, day) VALUES (?, ?, ?);";
    // Prepare statement
    $stmt = $conn->prepare($sql);
    // Bind parameters
    $stmt->bind_param("iis", $user_id, $recipe_id, $day);
    // execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
  //  header('Location: '.$recipe_link);

}
    

