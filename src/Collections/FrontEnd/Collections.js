function togglePopup() { 
    var modal = document.getElementById('popup-modal');
    var span = document.getElementsByClassName('close')[0];
    modal.style.display = 'none';
    const overlay = document.getElementById('popupOverlay'); 
    overlay.classList.toggle('show'); 
}

document.addEventListener('DOMContentLoaded', function() {

    // Get the modal element
    var modal = document.getElementById('popup-modal');

    // Get the button that opens the modal
    var btn = document.getElementById('new-collection-button');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName('close')[0];

    // When the user clicks the button, open the modal
    btn.onclick = function() {
    modal.style.display = 'block';
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
    modal.style.display = 'none';
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
    }

    // Create button click handler
    var createBtn = document.getElementById('create-collection-btn');

    createBtn.onclick = function() {
    var collectionName = document.getElementById('collection-name').value.trim();
    if (collectionName == '' || /\s/.test(collectionName)) {
        //alert('Collection name cannot be empty or contain space characters.');
        togglePopup();
        return;
    }

    // Close the modal
    modal.style.display = 'none';

    // AJAX request to create new collection
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'BackEnd/NewCollection.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                console.log('Creating collection with name:', collectionName);
                console.log('new collection request successful');
                window.location.reload();
            } else {
                console.error('Error saving recipe:', xhr.statusText);
            }
        }
    };
    // Prepare data to be sent in the request body
    var data = 'collection_name=' + encodeURIComponent(collectionName);
    // Send the request
    xhr.send(data);
    }

});