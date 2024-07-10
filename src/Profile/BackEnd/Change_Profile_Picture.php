<?php
include '../../GeneralBackEnd/ServerChecks.php';


if(isset($_FILES['image'])){
   

    // Check if User has access rights
    $profile_id = $_POST['profile_id'];
    $permission = $_POST['permission'];
    $profile_link = "../Profile.php";

    if ($permission == 0) {
        $ret_val = array("success" => false, "message" => "You do not have permission");
        $profile_link = "../Profile.php?profile_id=$profile_id"; 
        
        return $ret_val;
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
  
    

    $new_profile_picture = file_get_contents($_FILES['image']['tmp_name']);


    // Update username
    $sql = "UPDATE accounts SET profile_picture=? WHERE id=?";
    echo $user_id;
    // Prepare statement
    $stmt = $conn->prepare($sql);
    // Bind parameters
    $stmt->bind_param("si", $new_profile_picture, $profile_id);
    // execute the query
    $stmt->execute();

    header('Location: '.$profile_link);
}