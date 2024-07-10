
document.addEventListener('DOMContentLoaded', function() {
    // get collection id
    var collectionId = document.querySelector('.collection-id').value.trim();

    // get the delete button
    var deletButton = document.getElementById('delete-collection-button');
    deletButton.onclick = function() {
        
        // AJAX request to create new collection
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'BackEnd/DeleteCollection.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log('Deleteing collection');
                    window.location.href = 'Collections.php';
                } else {
                    console.error('Error deleting collection: ', xhr.statusText);
                }
            }
        };
        // Prepare data to be sent in the request body
        var data = 'collection_id=' + encodeURIComponent(collectionId);
        // Send the request
        xhr.send(data);
        }

    // Get the modal
    var modal = document.getElementById("myModal");

    // Fetch the hidden elements associated with the clicked recipe
    var recipeId = this.querySelector('.recipe-id').value;
    var creatorId = this.querySelector('.creator_id').value;

    // Get the links inside list items
    var recipeLinks = document.querySelectorAll(".recipeLink");

    // Iterate over each recipe link and attach a click event listener
    recipeLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior

            // Fetch the hidden elements associated with the clicked recipe
            recipeId = this.querySelector('.recipe-id').value;
            creatorId = this.querySelector('.creator_id').value;
            
            modal.style.display = "block"; // Open the modal
            //console.log("new listener added");
        });
    });

    // When the user clicks on view button, redirect to the recipe page
    document.getElementById("viewButton").onclick = function() {
        // display recipe
        window.location.href = "../Recipe/Recipe.php?recipe_id=" + recipeId + "&user_id=" + creatorId;
    }

    // When the user clicks on add button, perform add to collection action
    document.getElementById("removeButton").onclick = function() {

        // AJAX request to save the recipe
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'BackEnd/RemoveFromCollection.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                     window.location.reload();
                } else {
                    // Handle error response
                    console.error('Error updating collection:', xhr.statusText);
                }
            }
        };
        // Prepare data to be sent in the request body
        var data = 'collection_id=' + encodeURIComponent(collectionId) + "&recipe_id=" + encodeURIComponent(recipeId) + "&creator_id=" + encodeURIComponent(creatorId);
        // Send the request
        xhr.send(data);
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    }
);