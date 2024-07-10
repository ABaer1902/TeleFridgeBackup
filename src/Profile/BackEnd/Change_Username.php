<?php
include '../../GeneralBackEnd/ServerChecks.php';

if(isset($_POST['username'])){

    // Check if User has access rights
    $new_username = $_POST['username'];
    $profile_id = $_POST['profile_id'];
    $profile_link = "../Profile.php";
    


    $creds = get_credentials();
    $serverName = $creds["serverName"];
    $uid = $creds["uid"];
    $pass = $creds["pass"];
    $database = $creds["database"];

    $conn = new mysqli($serverName, $uid, $pass, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $new_username = htmlspecialchars($_POST['username']);

    $checkUsernameSql = "SELECT username FROM accounts WHERE username = ?";
    $stmt = $conn->prepare($checkUsernameSql);

    // Bind the username parameter to the prepared statement
    $stmt->bind_param("s", $new_username);
    $stmt->execute();
    $usernameResult = $stmt->get_result();
    


    if ($usernameResult->num_rows > 0) {
        //echo "<script>alert('Username is already in use. Please choose another one.'); window.location.href='$profile_link';</script> "; 
    
    }
    else{

        // Update username
        $sql = "UPDATE accounts SET username=? WHERE id=?";
        // Prepare statement
        $stmt = $conn->prepare($sql);
        // Bind parameters
        $stmt->bind_param("si", $new_username, $profile_id);

        // execute the query
        $stmt->execute();
        
        header('Location: '.$profile_link);
    }
    
}
?>

<html>

<head>

    <link href='https://fonts.googleapis.com/css?family=Luckiest Guy' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

</head>
<!-- Popup for ERRORS -->
<div id="popupOverlay" class="overlay-container"> 
    <div class="popup-box"> 
        <h1 style="color: #FBAE3B;">WARNING</h1> 
        <br>

        <p> This username is taken. </p>
        <br>

        <button class="btn-close-popup" onclick="history.back();"> 
        Close 
        </button>

    </div> 
</div> 


<script>
    function togglePopup() { 
    const overlay = document.getElementById('popupOverlay'); 
    overlay.classList.toggle('show'); 
    } 

    <?php 

    if ($usernameResult->num_rows > 0) {
        echo "togglePopup();";
    }
    
    ?>

</script>

<style>

    .message, h1{
        padding: 0;
        margin:0;
        font-family: 'Luckiest Guy';
        font-size: 50px;
        font-weight: 100;
        text-overflow: clip;
        color: #000000;
    }

    p, .btn-close-popup {
        font-family: 'Luckiest Guy';
        font-size: 40px;
        font-weight: 20;
    }

    .overlay-container { 
        display: none; 
        position: fixed; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(0, 0, 0, 0.6); 
        justify-content: center; 
        align-items: center; 
        opacity: 0; 
        transition: opacity 0.3s ease; 
    } 

    .popup-box { 
        background: #fff; 
        padding: 24px; 
        border-radius: 12px; 
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.4); 
        width: 320px; 
        text-align: center; 
        opacity: 0; 
        transform: scale(0.8); 
        animation: fadeInUp 0.5s ease-out forwards; 
        z-index: 99 !important;
    } 

    .btn-close-popup { 
        padding: 12px 24px; 
        border: none; 
        border-radius: 8px; 
        cursor: pointer; 
        transition: background-color 0.3s ease, color 0.3s ease; 
        font-size: 30px;
    } 

    .btn-close-popup { 
        margin-top: 12px; 
        background-color: #e74c3c; 
        color: #fff; 
    } 

    .btn-close-popup:hover {
        background-color: #f08175;
    }


    /* Keyframes for fadeInUp animation */ 
    @keyframes fadeInUp { 
        from { 
            opacity: 0; 
            transform: translateY(20px); 
        } 

        to { 
            opacity: 1; 
            transform: translateY(0); 
        } 
    } 

    /* Animation for popup */ 
    .overlay-container.show { 
        display: flex; 
        opacity: 1; 
    } 


</style>

</html>