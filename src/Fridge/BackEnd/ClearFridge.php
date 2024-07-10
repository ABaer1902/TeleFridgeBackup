<?php
// just a processing file so we dont check if user is logged in
include '../../GeneralBackEnd/ServerChecks.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") { 

    // get user id and collection name
    $user_id = get_user_id();

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

    $newval = NULL; // set fridge to null

    // UPDATE user's fridge ijn accounts table
    $sql = "UPDATE accounts SET fridge = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $newval, $user_id);
    $stmt->execute();
}

?>