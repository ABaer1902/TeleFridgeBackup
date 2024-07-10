<?php

// first check if user is logged in
checkLoggedIn();

// get user id
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

$sql = "SELECT c.id AS collection_id, c.name AS collection_name, c.creator_id
FROM collections AS c
WHERE c.creator_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<ol id="savesList">
    <?php
    // Step 3: Fetch and display data
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<li class='collections' style='list-style-type: none; text-decoration: none;'>";
            echo "<a class='collectionLink' href='SingleCollection.php?collection_id={$row['collection_id']}&creator_id={$row['creator_id']}'>";
            
            echo "<div class='collectionContent'>";
            echo "<input type='hidden' class='collection-id' value='{$row['collection_id']}'>";
            echo "<input type='hidden' class='creator-id' value='{$row['creator_id']}'>"; 
            echo "<input type='hidden' class='collection-name' value='{$row['collection_name']}'>";


            echo "<div class='name'>";
            echo "<p class='collectionMsg'>{$row['collection_name']}</p>";
            echo "</div>";

            echo "</div>";
            echo "</a>";
            echo "</li>";
        }
    } else {
        echo "<p class='recipeMsg'>Oops... You don't have any collections</p>";
    }
    $conn->close();
    ?>
</ol>
