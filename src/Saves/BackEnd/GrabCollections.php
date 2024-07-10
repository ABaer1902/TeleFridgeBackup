<?php

// Connect to the database
$creds = get_credentials();
$serverName = $creds["serverName"];
$uid = $creds["uid"];
$pass = $creds["pass"];
$database = $creds["database"];

$user_id = get_user_id();

$conn = new mysqli($serverName, $uid, $pass, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT c.id AS collection_id, c.name AS collection_name, c.creator_id
FROM collections AS c
WHERE c.creator_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();


// Step 3: Fetch and display data
echo "<ol id='modalsCollections'>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {

        echo "<div class='name'>";
        echo "<p class='collectionMsg'>{$row['collection_name']}</p>";

        echo "<input type='hidden' class='collection-id' value='{$row['collection_id']}'>";
        echo "<input type='hidden' class='creator-id' value='{$row['creator_id']}'>"; 

        echo "</div>";
    }
}
echo "</ol>";

$conn->close();

?>