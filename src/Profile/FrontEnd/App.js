function logout(){
    //console.log("Running logout");
    if(checkCookie()){
        console.log("Cookie Found; Destroying Cookie");
        document.cookie = "username=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; Secure; SameSite=None;";
    }else{
        console.log("No Cookie was Found");
    }
    
    // Redirect the user to the login page or perform any other logout actions
    window.location.href = "../Landing/Landing.php";
}

function checkCookie() {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].trim();
        if (cookie.startsWith("username=")) {
            // 'username' cookie found
            return true;
        }
    }
    // 'username' cookie not found
    return false;
}

function backButton(){
    history.back();
}

function getCookie(name) {
    let cookieArray = document.cookie.split(';'); // Splitting the document.cookie string
    for(let i = 0; i < cookieArray.length; i++) {
        let cookie = cookieArray[i];
        while (cookie.charAt(0) === ' ') cookie = cookie.substring(1); // Trimming any leading spaces
        if (cookie.indexOf(name + '=') === 0)
            return cookie.substring(name.length + 1, cookie.length); // Extracting and returning the cookie value
    }
    return ""; // Return empty string if the cookie is not found
}

  
function togglePopup() { 
    const overlay = document.getElementById('popupOverlay'); 
    overlay.classList.toggle('show'); 
} 

function togglePopupN() { 
    const overlay = document.getElementById('popupOverlayN'); 
    overlay.classList.toggle('show'); 
} 

function togglePopupF() {
    const overlay = document.getElementById('popupOverlayF');
    overlay.classList.toggle('show');
}

function togglePopupFL() {
    const overlay = document.getElementById('popupOverlayFL');
    overlay.classList.toggle('show');
}

document.addEventListener('DOMContentLoaded', function() {

    if (localStorage.getItem('newrecipe')) {
        // check for new recipe flag
        //alert("New recipe added.");

        // remove flag
        localStorage.removeItem('newrecipe');
    }


    var username = getCookie('username');
    var followButton = document.getElementById('follow-button');
    var picButton = document.getElementById('picbtn');
    var nameButton = document.getElementById('username');
    var flistButton = document.getElementById('show-follow');
    var fllistButton = document.getElementById('show-following');

    followButton.addEventListener('click', function(){
        if(!username) {
            window.location = '../Login/Login.php';
        } else {
            console.log('followed!');

            // add php file here
        }
    });

    picButton.addEventListener('click', function(){
        if (!username) {
          window.location = '../Login/Login.php';
        } else { 
          togglePopup();
        }
    });

    nameButton.addEventListener('click', function(){
        if (!username) {
          window.location = '../Login/Login.php';
        } else {
          togglePopupN();
        }
    });

    flistButton.addEventListener('click', function(){
        if (!username) {
            window.location = '../Login/Login.php';
          } else {
            togglePopupF();
          }
    });

    fllistButton.addEventListener('click', function(){
        if (!username) {
            window.location = '../Login/Login.php';
          } else {
            togglePopupFL();
          }
    });
    document.getElementById("saveBtn").addEventListener("click", function() {
        var form = document.getElementById("bioForm");
        var formData = new FormData(form);

        var xhr = new XMLHttpRequest();
        xhr.open("POST", form.action, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
            } else {
                console.error(xhr.responseText);
            }
        };
        xhr.send(formData);
    });

    document.getElementById("cancelBtn").addEventListener("click", function() {
        // reload the page
        window.location.reload();
    });

});