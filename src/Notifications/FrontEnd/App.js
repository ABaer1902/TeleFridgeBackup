
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

function togglePopup() { 
    const overlay = document.getElementById('popupOverlay'); 
    overlay.classList.toggle('show'); 
} 

function togglePopupN() { 
    const overlay = document.getElementById('popupOverlayN'); 
    overlay.classList.toggle('show'); 
} 