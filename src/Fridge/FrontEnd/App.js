//Stuff Taken From NewRecipe

// Function to add ingredient
function addIngredient() {
    var ingredientInput = document.getElementById("ingredientInput");
    var ingredient = ingredientInput.value.trim(); // Trim whitespace
    var result = validateInput();

    // Logs the ingredient
    if (result != null) {
        //alert(result);
        return;
    } else {
        console.log("Ingredient added:", ingredient);
    }
    
    // Add the ingredient to the list
    var ingredientList = document.getElementById("ingredientUl");
    var li = document.createElement("li");
    li.textContent = ingredient;
    ingredientList.appendChild(li);

    // Clear the input field
    document.getElementById("ingredientInput").value = "";

    // Update hidden input field with comma-separated list of ingredients
    updateIngredientList();
}

function removeLastIngredient() {
    var ul = document.getElementById("ingredientUl");
    var lastItem = ul.lastElementChild;
    if (lastItem) {
        ul.removeChild(lastItem);
    }

    // Update hidden input field with comma-separated list of ingredients
    updateIngredientList();
}

function removeIngredient(id) {
    var ul = document.getElementById("ingredientUl");
    var specificItem = document.getElementById(String(id)); // Use document.getElementById() to get the element by its ID
    console.log(specificItem + '#' + String(id));
    if (specificItem) {
        ul.removeChild(specificItem);
        console.log('Removed item ' + String(id));
    }

    // Update hidden input field with comma-separated list of ingredients
    updateIngredientList();
}


// Function to update hidden input field with comma-separated list of ingredients
function updateIngredientList() {
    var ingredients = Array.from(document.querySelectorAll('#ingredientUl li'))
        .map(li => li.textContent.trim())
        .join(','); // Join ingredients with commas

    // Update hidden input field value
    document.getElementById('ingredientListInput').value = ingredients;
    // printing the list
    console.log('ingredient list: ', ingredients);
}

//Checks for blank input or containing comma
function validateInput() {
    var ingredientInput = document.getElementById('ingredientInput').value.trim();

    if (ingredientInput === "") {
        return "Error: No Input";
    }

    if (ingredientInput.includes(",")) {
        return "Error: Illegal Character: ','";
    }
    return null; // Return null if no validation errors
}

//Checks for blank input ingredients list
function validateList() {
    var ingredientInput = document.getElementById('ingredientListInput').value.trim();

    // Check if the input is empty or contains only spaces and commas
    if (/^[\s,]*$/.test(ingredientInput)) {
        return "Error: Input is empty or contains only spaces and commas";
    }

    // Check if the input lacks alphanumeric characters
    if (!/[a-zA-Z0-9]/.test(ingredientInput)) {
        return "Error: Input contains no alphanumeric characters";
    }

    return null; // Return null if no validation errors
}


// ----------------------------------------------

document.addEventListener('DOMContentLoaded', function() {
    clearButton = document.getElementById('clearButton').addEventListener('click', function(event) {
        
        // Submit the form
        document.getElementById('fridgelist').submit();
        // AJAX request to clear user's fridge
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'BackEnd/ClearFridge.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                } else {
                    console.error('Error clearing fridge:', xhr.statusText);
                }
            }
        };
        xhr.send();
 
        var ingredientList = document.getElementById('ingredientUl');
        while (ingredientList.firstChild) {
            ingredientList.removeChild(ingredientList.firstChild);
        }
        // Update hidden input field value
        document.getElementById('ingredientListInput').value = null;
        updateIngredientList();
        // Submit the form
        document.getElementById('fridgelist').submit(); // reloads window
    });

    document.getElementById('submitButton').addEventListener('click', function(event) {
        // Call the validateInput function
        var validationResult = validateList();

        // Check if validation failed
        if (validationResult === null) {
            // If validation is successful, update the ingredient list
            // alert("Fridge has been updated!!");
        } else {
            // If validation failed, prevent form submission
            alert(validationResult);
            event.preventDefault();
        }
    });
});