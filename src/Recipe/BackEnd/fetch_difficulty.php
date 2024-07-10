<?php
if (isset($_GET)) {
    session_start();
    
    include '../../GeneralBackEnd/ServerChecks.php';
    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];
    
    // get the user_id and recipe_id
    $user_id = get_user_id();
    if ($user_id == NULL) {
        return;
    }
    $recipe_id = isset($_SESSION['recipe_id']) ? htmlspecialchars($_SESSION['recipe_id']) : null;

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT difficulty FROM likes_dislikes WHERE recipe_id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $recipe_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $difficulty = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (empty($difficulty)) {
        //echo "nothing yet";
        //echo $recipe_id;
        $sql = "SELECT difficulty FROM recipes WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        //echo json_encode($result);
        $difficulty = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    
    //$difficulty = $difficulty[0]["difficulty"];
    mysqli_free_result($result);
    //ob_clean();
    //echo '----';
    echo json_encode($difficulty);
    //echo '----';
}
