<?php

include '../../GeneralBackEnd/ServerChecks.php';

function delete($recipe_id) {
    session_start();

    checkLoggedIn();
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

    // check that the logged in user created that recipe

    $sql = "SELECT creator_id FROM recipes WHERE id=$recipe_id";
    $result = mysqli_query($conn, $sql);
    $creator_id = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]["creator_id"];
    mysqli_free_result($result);

    if ($user_id != $creator_id) {
        $ret_val = array("Success" => false, "Message" => "You do not have permission to delete that recipe");
        return $ret_val;
    }

    // delete the recipe from recipes table
    $sql = "DELETE FROM recipes WHERE id=$recipe_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // delete the recipe from likes_dislikes table
    $sql = "DELETE FROM likes_dislikes WHERE recipe_id=$recipe_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // delete the recipe from saves table
    $sql = "DELETE FROM saves WHERE recipe_id=$recipe_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $ret_val = array("Success" => true, "Message" => "Recipe Successfully Deleted");
    return $ret_val;
}

if (isset($_POST)) {
    $data = file_get_contents("php://input");
    $recipe_id = json_decode($data, true);
    
    echo json_encode(delete($recipe_id));
}
