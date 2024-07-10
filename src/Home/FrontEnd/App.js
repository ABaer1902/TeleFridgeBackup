'use strict';

function sortDD() {
  document.getElementById("sortDropdown").classList.toggle("show");
}

function dietDD() {
  document.getElementById("dietDropdown").style.display = "block";
}

function ingredientsDD() {
  document.getElementById("ingredientDropdown").style.display = "block";
}


window.onclick = function(event) {
    if(!event.target.matches('.dropbtn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        var i;
        for(i=0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }
}
// const reactElem = React.createElement;


/*
class RegisterButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = { clicked: false };
  }

  render() {

    return reactElem(
      'button',
      { onClick: () => getInputs() },
      'REGISTER'
    );
  }
}
*/

function getInputs(){
  //bruh literally this doesn't matter
  let search = document.getElementById('search').value;
  let beef = document.getElementById('beef').value;
  let ingredients = document.getElementById('ingredients').value;

  // create an othat holds user inoputs
  let registerData = {
    Search: search,
    Beef: beef,
    Ingredients: ingredients
  };

  // form AJAX request
  let xhr = new XMLHttpRequest();

  xhr.open("POST", "../Home.php", true);
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      console.log(xhr.responseText); // Log the response from the server
    } else {
      console.log("POST request not made");
      console.log(registerData)
    }
  };
  xhr.send(JSON.stringify(registerData));

}


/*
// connects react component with div
//  explore button
const registerDom = document.querySelector('#registerButton');
const registerRoot = ReactDOM.createRoot(registerDom);
registerRoot.render(reactElem(RegisterButton));
*/

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

document.addEventListener('DOMContentLoaded', function() {
  var username = getCookie('username');

  var fridgebutton = document.getElementById('fridgeFilterButton');
  if (fridgebutton) {
    fridgebutton.addEventListener('click', function() {
      // check if user is a guest
      if (!username) {
        window.location = '../Login/Login.php';
      } 
  });
  }


  var button = document.getElementById('feelingHungryButton');
  if (button) {
    button.addEventListener('click', function() {
          
          console.log("user is hungry!");
          
          // AJAX request to saves.php
          var xhr = new XMLHttpRequest();
          xhr.open('POST', 'BackEnd/GenerateRandomRecipe.php', true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onreadystatechange = function() {
              if (xhr.readyState === 4) {
                  if (xhr.status === 200) {
                      // Parse the JSON response
                      var responseData = JSON.parse(xhr.responseText);
                      var randomRecipeId = responseData.recipe_id;
                      var randomUserId = responseData.creator_id;

                      // Redirect to recipe.php with the generated user_id and recipe_id
                      window.location.href = '../Recipe/Recipe.php?user_id=' + randomUserId + '&recipe_id=' + randomRecipeId;
                  } else {
                      // Handle error response
                      console.error('Error saving recipe:', xhr.statusText);
                  }
              }
          };

          // Send the request
          xhr.send();

      });
  } else {
      console.log("hungry button not found");
  }
});
