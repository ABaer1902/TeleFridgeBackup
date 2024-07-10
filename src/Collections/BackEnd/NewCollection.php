<?php
// just a processing file so we dont check if user is logged in
include '../../GeneralBackEnd/ServerChecks.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") { 

    // get user id and collection name
    $user_id = get_user_id();
    $collection_name = htmlspecialchars($_POST['collection_name']);

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

    // INSERT into collections table
    $sql = "INSERT INTO collections (creator_id, name) VALUES (?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $user_id, $collection_name);
    $stmt->execute();
    $result = $stmt->get_result();
}

?>