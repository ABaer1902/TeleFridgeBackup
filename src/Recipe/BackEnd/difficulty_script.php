<?php

function set_new_difficulty($difficulty) {
    session_start();
    // check valid input
    if ($difficulty > 5) {
        $difficulty = 5;
    }
    if ($difficulty < 0) {
        $difficulty = 0;
    }

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
        return;
    }
    $recipe_id = isset($_SESSION['recipe_id']) ? $_SESSION['recipe_id'] : null;

    // store new difficulty in user metrics

    // check if user has interacted with this recipe and create record if they havent
    $sql = "SELECT reaction FROM likes_dislikes WHERE (recipe_id=? AND user_id=?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $recipe_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usermetrics = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // check if they user has interacted with this recipe before and add them to the database
    if (empty($usermetrics)) {
        $zero = 0;
        $sql = "INSERT INTO likes_dislikes (recipe_id, user_id, reaction, difficulty) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiii', $recipe_id, $user_id, $zero, $zero);
        $stmt->execute();
    }
    mysqli_free_result($result);

    // upload to the user db
    $sql = "UPDATE likes_dislikes SET difficulty = ? WHERE recipe_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $difficulty, $recipe_id, $user_id);
    $stmt->execute();

    return $difficulty;
}

if (isset($_POST)) {
    $data = file_get_contents("php://input");
    $difficulty = json_decode($data, true)["difficulty"];
    $result = set_new_difficulty($difficulty);
    ob_clean();
    echo json_encode($result);
}
