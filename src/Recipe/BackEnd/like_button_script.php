<?php

function like_dislike_button($like){
    session_start();
    
    include '../../GeneralBackEnd/ServerChecks.php';
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
     
    // get the user_id and recipe_id
    $user_id = get_user_id();
    if ($user_id == NULL) {
    // echo "no user";
        return;
    }
    $recipe_id = isset($_SESSION['recipe_id']) ? $_SESSION['recipe_id'] : null;

    // get recipe data
    $sql = "SELECT id, likes, dislikes, creator_id FROM recipes WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipedata = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
    mysqli_free_result($result);

    $creator_id = $recipedata["creator_id"];

    // get user data
    $sql = "SELECT reaction FROM likes_dislikes WHERE (recipe_id=$recipe_id AND user_id=$user_id)";
    $result = mysqli_query($conn, $sql);
    $usermetrics = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // check if they user has interacted with this recipe before and add them to the database
    if (empty($usermetrics)) {
        $zero = 0;
        $usermetrics = array("reaction" => 0);
        $sql = "INSERT INTO likes_dislikes (recipe_id, user_id, reaction, difficulty, creator_id) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiiii', $recipe_id, $user_id, $zero, $zero, $creator_id);
        $stmt->execute();
    }
    else {
        $usermetrics = $usermetrics[0];
    }
    mysqli_free_result($result);

    $likes_count = $recipedata["likes"];
    $dislikes_count = $recipedata["dislikes"];
    $reaction = $usermetrics["reaction"];
    

    // action was pressing like button
    if ($like) {
        // action was liking
        if ($reaction == 0) {
            $likes_count = $recipedata["likes"] + 1;
            $reaction = 1;
        }
        else if ($reaction == -1) {
            $likes_count = $recipedata["likes"] + 1;
            $dislikes_count = $recipedata["dislikes"] - 1;
            $reaction = 1;
        }
        // action was unliking
        else {
            $likes_count = $recipedata["likes"] - 1;
            $reaction = 0;
        }
    }
    // action was pressing dislike button
    else {
        // actions was disliking
        if ($reaction == 0) {
            $dislikes_count = $recipedata["dislikes"] + 1;
            $reaction = -1;
        }
        else if ($reaction == 1) {
            $dislikes_count = $recipedata["dislikes"] + 1;
            $likes_count = $recipedata["likes"] - 1;
            $reaction = -1;
        }
        // action was undisliking
        else {
            $dislikes_count = $recipedata["dislikes"] - 1;
            $reaction = 0;
        }
    }

    $ret_val = array("like_count" => $likes_count, "dislike_count" => $dislikes_count, "reaction" => $reaction);


    // Update both DBs

    // upload to the user db
    $currentTimestamp = date('Y-m-d H:i:s');
    $sql = "UPDATE likes_dislikes SET reaction=?, timestamp=? WHERE recipe_id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isii', $reaction, $currentTimestamp, $recipe_id, $user_id);
    $stmt->execute();

    // upload to the recipe db
    $sql = "UPDATE recipes SET likes=?, dislikes=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iii', $likes_count, $dislikes_count, $recipe_id);
    $stmt->execute();

    return $ret_val;
}


if (isset($_POST)) {
    $data = file_get_contents("php://input");
    $like = json_decode($data, true)["like"];
    $result = like_dislike_button($like);
    echo json_encode($result);
}
