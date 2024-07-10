'use strict';

const reactElem = React.createElement;

function getInputs(){
    let password = document.getElementById('password').value;
    let password2 = document.getElementById('confirmPassword').value;

    // check that the passwords match
    if(password != password2){
        console.log("Passwords do not match");
    }else{
        console.log("Password: "+password);
    }

    //get query string token (should only run if php already authenticated token)
    //const searchParams = new URLSearchParams(window.location.search);
    //let token = searchParams.get("token");
    //console.log(searchParams.get("token"));

    // create an othat holds user inputs
    let resetData = {
        Password: password
        //Token: token
    };

    // form AJAX request
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../Recover.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
        console.log(xhr.responseText); // Log the response from the server
        } else {
        console.log("POST request not made");
        console.log(resetData);
        }
    };
    xhr.send(JSON.stringify(resetData));
}
