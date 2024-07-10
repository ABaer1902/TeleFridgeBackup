function validateForm() {
    var recipeName = document.getElementById('recipe-name').value.trim();
    var description = document.getElementById('description').value.trim();
    var instructions = document.getElementById('instructions').value.trim();
    var calories = document.getElementById('recipe-calories').value.trim();
    var ingredientsList = document.getElementById('ingredientListInput').value.trim();

    if (recipeName === "") {
        return "You still need a recipe name.";
    }

    // check difficulty
    var dif = document.getElementById('difficultyInput').value;
    if (dif == 0) {
        return "You still need to set the difficulty.";
    }

    // check duration
    var hr = document.getElementById('hoursInput').value;
    var min = document.getElementById('minutesInput').value;
    if (isNaN(hr) && isNaN(min)) {
        return "Recipe time must be greater than 0.";
    } else if (hr == 0 && min == 0) {
        return "Recipe time must be greater than 0.";
    }

    if (calories == ""){
        return "You still need to set the calories.";
    }

    if (description === "") {
        return "You still need a description.";
    }

    if (instructions === "") {
        return "You still need instructions.";
    }

    if (ingredientsList === "") {
        return "No ingredients provided.";
    }

    console.log("Form was fully filled out");
    return null; // Return null if no validation errors
}

function clearedForm() {
    // Clear form inputs
    var inputs = document.getElementById("newRecipeForm").querySelectorAll("input");
    inputs.forEach(function(input) {
        input.value = "";
    });

    // Clear the ordered list
    var ingredientList = document.getElementById("ingredientUl");
    ingredientList.innerHTML = ""; // Remove all list items
    
    // Set the selected difficulty as the value of the input field
    document.getElementById('difficultyInput').value = "";

    document.getElementById('recipe-calories').value = "";
    document.getElementById('description').value = "";
    document.getElementById('instructions').value = "";
    
    // Put all stars back to grey
    const star_1 = document.getElementById("star-1");
    const star_2 = document.getElementById("star-2");
    const star_3 = document.getElementById("star-3");
    const star_4 = document.getElementById("star-4");
    const star_5 = document.getElementById("star-5");
    // star 1
    star_1.src = "FrontEnd/imgs/gray_star.png";
    star_1.alt = "Gray Star";
    // star 2
    star_2.src = "FrontEnd/imgs/gray_star.png";
    star_2.alt = "Gray Star";
    // star 3
    star_3.src = "FrontEnd/imgs/gray_star.png";
    star_3.alt = "Gray Star";
    // star 4
    star_4.src = "FrontEnd/imgs/gray_star.png";
    star_4.alt = "Gray Star";
    //star 5
    star_5.src = "FrontEnd/imgs/gray_star.png";
    star_5.alt = "Gray Star";
    setDifficulty(1);
}


function difficulty_button(pressed){
    // Handle changing image of stars
    const star_1 = document.getElementById("star-1");
    const star_2 = document.getElementById("star-2");
    const star_3 = document.getElementById("star-3");
    const star_4 = document.getElementById("star-4");
    const star_5 = document.getElementById("star-5");
  
    if (pressed == 1) {
      // star 1
      star_1.src = "FrontEnd/imgs/yellow_star.png";
      star_1.alt = "Yellow Star";
      // star 2
      star_2.src = "FrontEnd/imgs/gray_star.png";
      star_2.alt = "Gray Star";
      // star 3
      star_3.src = "FrontEnd/imgs/gray_star.png";
      star_3.alt = "Gray Star";
      // star 4
      star_4.src = "FrontEnd/imgs/gray_star.png";
      star_4.alt = "Gray Star";
      //star 5
      star_5.src = "FrontEnd/imgs/gray_star.png";
      star_5.alt = "Gray Star";
      setDifficulty(1);
    }
  
    else if (pressed == 2) {
      // star 1
      star_1.src = "FrontEnd/imgs/yellow_star.png";
      star_1.alt = "Yellow Star";
      // star 2
      star_2.src = "FrontEnd/imgs/yellow_star.png";
      star_2.alt = "Yellow Star";
      // star 3
      star_3.src = "FrontEnd/imgs/gray_star.png";
      star_3.alt = "Gray Star";
      // star 4
      star_4.src = "FrontEnd/imgs/gray_star.png";
      star_4.alt = "Gray Star";
      //star 5
      star_5.src = "FrontEnd/imgs/gray_star.png";
      star_5.alt = "Gray Star";
      setDifficulty(2);
    }
  
    else if (pressed == 3) {
      // star 1
      star_1.src = "FrontEnd/imgs/yellow_star.png";
      star_1.alt = "Yellow Star";
      // star 2
      star_2.src = "FrontEnd/imgs/yellow_star.png";
      star_2.alt = "Yellow Star";
      // star 3
      star_3.src = "FrontEnd/imgs/yellow_star.png";
      star_3.alt = "Yellow Star";
      // star 4
      star_4.src = "FrontEnd/imgs/gray_star.png";
      star_4.alt = "Gray Star";
      //star 5
      star_5.src = "FrontEnd/imgs/gray_star.png";
      star_5.alt = "Gray Star";
      setDifficulty(3);
    }
  
    else if (pressed == 4) {
      // star 1
      star_1.src = "FrontEnd/imgs/yellow_star.png";
      star_1.alt = "Yellow Star";
      // star 2
      star_2.src = "FrontEnd/imgs/yellow_star.png";
      star_2.alt = "Yellow Star";
      // star 3
      star_3.src = "FrontEnd/imgs/yellow_star.png";
      star_3.alt = "Yellow Star";
      // star 4
      star_4.src = "FrontEnd/imgs/yellow_star.png";
      star_4.alt = "Yellow Star";
      //star 5
      star_5.src = "FrontEnd/imgs/gray_star.png";
      star_5.alt = "Gray Star";      
      setDifficulty(4);
    }
  
    else {
      // star 1
      star_1.src = "FrontEnd/imgs/yellow_star.png";
      star_1.alt = "Yellow Star";
      // star 2
      star_2.src = "FrontEnd/imgs/yellow_star.png";
      star_2.alt = "Yellow Star";
      // star 3
      star_3.src = "FrontEnd/imgs/yellow_star.png";
      star_3.alt = "Yellow Star";
      // star 4
      star_4.src = "FrontEnd/imgs/yellow_star.png";
      star_4.alt = "Yellow Star";
      //star 5
      star_5.src = "FrontEnd/imgs/yellow_star.png";
      star_5.alt = "Yellow Star";
      setDifficulty(5);
    }
}

function backButton(){
    history.back();
}

function setDifficulty(difficulty) {
    selectedDifficulty = difficulty;
    // Highlight selected difficulty and remove highlight from others
    var buttons = document.querySelectorAll('.difficulty');
    buttons.forEach(function(button) {
      var buttonDifficulty = parseInt(button.textContent);
      /*
      if (buttonDifficulty <= selectedDifficulty) {
        //button.style.backgroundColor = '#ffc107'; // Change to desired highlight color
        button.style.backgroundImage = "url('FrontEnd/imgs/gray_star.png')";
      } else {
        //button.style.backgroundColor = '#f0f0f0'; // Change to default button color
        button.style.backgroundImage = "url('FrontEnd/imgs/gray_star.png')";
      }
      */
    });
  
    // Set the selected difficulty as the value of the input field
    document.getElementById('difficultyInput').value = selectedDifficulty;
    
    // You can perform additional actions here, such as submitting the rating to a server
    console.log("Selected difficulty:", selectedDifficulty);
}

function clearInput(input) {
    if (input.value === '') {
        input.value = '0';
    }
}

// sets duration, this handles formatting problems like (ex. 00001 becomes 1)
function setDuration() {
    const hoursInput = document.getElementById('hoursInput');
    const minutesInput = document.getElementById('minutesInput');

    // Get the values from the input fields
    let hours = parseInt(hoursInput.value.trim(), 10);
    let minutes = parseInt(minutesInput.value.trim(), 10);

    // Validate and format hours
    hours = isNaN(hours) ? 0 : hours;

    // Validate and format minutes
    minutes = isNaN(minutes) ? 0 : minutes;

    // Update input fields with formatted values
    hoursInput.value = hours.toString();
    minutesInput.value = minutes.toString();

    // Do something with the valid values
    console.log("Hours:", hours, "Minutes:", minutes);
}

// Function to add ingredient
function addIngredient() {
    var ingredientInput = document.getElementById("ingredientInput");
    var ingredient = ingredientInput.value.trim(); // Trim whitespace

    // Logs the ingredient
    if (ingredient == "") {
        console.log("no empty input");
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

// Function to update hidden input field with comma-separated list of ingredients
function updateIngredientList() {
    var ingredients = Array.from(document.querySelectorAll('#ingredientUl li'))
        .map(li => li.textContent.trim())
        .join(','); // Join ingredients with commas

    // Update hidden input field value
    document.getElementById('ingredientListInput').value = ingredients;
}




// EVENT LISTENER SECTION -----------------------------------------------------------------

// need to parse the unordered list and add the ingredients to form
// grabs the form after "submission" parses thee lista dn attaches the info
// then actually submits the form
