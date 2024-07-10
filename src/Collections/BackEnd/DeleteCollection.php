<?php
include '../../GeneralBackEnd/ServerChecks.php';
checkLoggedIn();

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
// get collection id
$collection_id = htmlspecialchars($_POST['collection_id']);

// get user id
$user_id = get_user_id();
// get creator id
$creator_id = -1;
$sql = "SELECT creator_id FROM collections WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $collection_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $creator_id = $row['creator_id'];
}

// block request if user id and creator id dont match
if ($creator_id != $user_id) {
    echo $creator_id;
    echo $user_id;
    echo "invalid request. collection doesn't belong to you.\n";
    return;
}

// delete the collection recipes table
$sql = "DELETE FROM collection_recipes WHERE collection_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $collection_id);
$stmt->execute();

// delete the collection from collections table
$sql = "DELETE FROM collections WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $collection_id);
$stmt->execute();

echo "deleted successfully.\n"
?>