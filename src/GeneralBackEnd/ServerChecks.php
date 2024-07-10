<?php

function get_credentials(){
    $serverName = "oceanus.cse.buffalo.edu:3306";
    $database = "cse442_2024_spring_team_ai_db";
    $uid = "adambaer";
    $pass = "50338920";

    //$serverName = "localhost";
    //$database = "mysql";
    //$uid = "root";
    //$pass = "";
    
    $creds = array("serverName" => $serverName, "database" => $database,"uid" => $uid, "pass" =>$pass);
    return $creds;
}

function checkLoggedIn() {
    // if they have a cookie redirect them to home
    if (!isset($_COOKIE['username'])) {
        header('Location: ../Login/Login.php');
        exit();
    }
}

// Returns true if the user is a guest false otherwise
function isGuest() {
    if (!isset($_COOKIE['username'])) {
        return True;
    }
    return False;
}

function get_user_id() {
    // checks if user is logged in
    if (!isset($_COOKIE['username'])) {
        return NULL;
    } else {
        $cookieValue = htmlspecialchars($_COOKIE['username']);
    }

    // checks if user has a non empty fridge
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

    // Get user ID
    $query = "SELECT id FROM accounts WHERE cookie = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $cookieValue);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any row is returned
    if ($result->num_rows > 0) {
        // Fetch the row
        $row = $result->fetch_assoc();
        // Get id number
        $id = $row['id'];
        // echo $id;
        // echo "user exists";
        return $id;
    } else {
        // echo "no user";
        return NULL; // User does not exist
    }
}