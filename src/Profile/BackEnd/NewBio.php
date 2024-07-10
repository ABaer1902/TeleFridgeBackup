<?php 
include '../../GeneralBackEnd/ServerChecks.php';

// check that the user is logged in
checkLoggedIn();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // get profile/bio data
    $user_id = get_user_id();
    $bio = htmlspecialchars($_POST["bio"]);

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

    // check if they have already saved this recipe
    $sql = "UPDATE accounts SET bio = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $bio, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>