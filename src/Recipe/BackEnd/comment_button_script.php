<?php

function comment_button($comment){
    session_start();
    
    include '../../GeneralBackEnd/ServerChecks.php';

    // dont let user comment a blank comment
    if ($comment == "" or strlen($comment) > 120 or strlen(trim($comment)) == 0){
        return array("success" => false);
    }

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

    // insert the comment into the database
    $sql = "INSERT INTO comments (user_id, recipe_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $recipe_id, $comment);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    return array("success" => true);
}


if (isset($_POST)) {
    $data = file_get_contents("php://input");
    $content = json_decode($data, true)["content"];
    $result = comment_button(htmlspecialchars($content));
    echo json_encode($result);
}
