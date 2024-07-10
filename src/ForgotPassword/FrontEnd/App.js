'use strict';

const reactElem = React.createElement;

function getInputs(){
    let email = document.getElementById('email').value;

    console.log("Email: "+ email);

    // create an othat holds user inputs
    let resetData = {
        Email: email
    };

    // form AJAX request
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../ForgotPassword.php", true);
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
