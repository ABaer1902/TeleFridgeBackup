'use strict';

const likeButton = document.getElementById("like-button");
const dislikeButton = document.getElementById("dislike-button");
const difficultyButton1 = document.getElementById("difficulty1");
const difficultyButton2 = document.getElementById("difficulty2");
const difficultyButton3 = document.getElementById("difficulty3");
const difficultyButton4 = document.getElementById("difficulty4");
const difficultyButton5 = document.getElementById("difficulty5");

function like_dislike_click(like){
  let data = {"like": like};
  
  fetch("BackEnd/like_button_script.php", {
    "method": "POST",
    "headers": {
      "Content-Type": "application/json; charset=utf-8"
    },
    "body": JSON.stringify(data)
  }).then(function(response){
    // console.log('HERE:')
    // console.log(response.text());
    return response.text();
  }).then(function(data){
    data = JSON.parse(data);
    display_new_likes(data);
  })
}

function difficulty_click(difficulty){
  let data = {"difficulty": difficulty};

  fetch("BackEnd/difficulty_script.php", {
    "method": "POST",
    "headers": {
      "Content-Type": "application/json; charset=utf-8"
    },
    "body": JSON.stringify(data)
  }).then(function(response){
    return response.text();
  }).then(function(data){
    data = JSON.parse(data);
    display_difficulty(data);
  })
}

function display_new_likes(data){
  const like = document.getElementById("like-picture");
  const dislike = document.getElementById("dislike-picture");
  // fill like button
  if (data["reaction"] == 1){
    like.src = "FrontEnd/imgs/heart_filled.png";
    like.alt = "Heart Filled ";
    dislike.src = "FrontEnd/imgs/broken_heart_outline.png";
    dislike.alt = "Brken Heart Outline";
  }
  // both outline
  else if (data["reaction"] == 0) {
    like.src = "FrontEnd/imgs/heart_outline.png";
    like.alt = "Heart Outline";
    dislike.src = "FrontEnd/imgs/broken_heart_outline.png";
    dislike.alt = "Brken Heart Outline";
  }
  // fill dislike button
  else {
    like.src = "FrontEnd/imgs/heart_outline.png";
    like.alt = "Heart Outline";
    dislike.src = "FrontEnd/imgs/broken_heart_filled.png";
    dislike.alt = "Broken Heart Filled";
  }
  document.getElementById("like-count").textContent = data["like_count"];
  document.getElementById("dislike-count").textContent = data["dislike_count"];
}


// Define Difficulty buttons

function display_difficulty(pressed){
  if (pressed == "grab"){
    console.log(fetch_difficulty());
    return;
  }
  const star_1 = document.getElementById("star-1");
  const star_2 = document.getElementById("star-2");
  const star_3 = document.getElementById("star-3");
  const star_4 = document.getElementById("star-4");
  const star_5 = document.getElementById("star-5");

  if (pressed == 0) {
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

  } else if (pressed == 1) {
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
  }
}

// function fetch_difficulty(){
//   fetch("BackEnd/fetch_difficulty.php", {
//     "method": "GET",
//     "headers": {
//       "Content-Type": "application/json; charset=utf-8"
//     }
//   }).then(function(response){
//     //console.log(response.text());
//     return response.text();
//   }).then(function(data){
//     data = parseInt(JSON.parse(data));
//     console.log(data);
//     display_difficulty(data);
//   })
// }

function fetch_difficulty(){
  fetch("BackEnd/fetch_difficulty.php", {
    method: "GET" 
  }).then(function(response){
    return response.text();
  }).then(function(data){
    let parsedData = JSON.parse(data); // Parse the string to a JavaScript object
    if (parsedData.length > 0 && parsedData[0].hasOwnProperty('difficulty')) {
        let difficulty = parsedData[0].difficulty;
        console.log(difficulty); 
        display_difficulty(difficulty);
    }
  }).catch(function(error){
    console.error("Error fetching difficulty:", error);
  });
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


function backbtn(meal_pressed) {
  if (meal_pressed == 1){
    history.go(-2);
  }
  else{
    history.back();
  }
}

// Save button click event handler
document.addEventListener('DOMContentLoaded', function() {
  var username = getCookie('username');
  var likeButton = document.getElementById('like-button');
  var dislikeButton = document.getElementById('dislike-button');
  var saveButton = document.getElementById('save-button');
  var mealButton = document.getElementById('meal-button');
  var submitButton = document.getElementById('submit-button');
  var form = document.getElementById('mealplan');

  likeButton.addEventListener('click', function(){
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      console.log('like action');
      like_dislike_click(true);
    }
  });

  dislikeButton.addEventListener('click', function(){
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      console.log('dislike action');
      like_dislike_click(false);
    }
  });

  /*
  // star 1
  difficultyButton1.addEventListener("click", function() {
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      difficulty_click(1);
    }
  });

  // star 2
  difficultyButton2.addEventListener("click", function() {
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      difficulty_click(2);
    }
  });

  // star 3
  difficultyButton3.addEventListener("click", function() {
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      difficulty_click(3);
    }
  });

  // star 4
  difficultyButton4.addEventListener("click", function() {
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      difficulty_click(4);
    }
  });

  // star 5
  difficultyButton5.addEventListener("click", function() {
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      difficulty_click(5);
    }
  });
  */

  mealButton.addEventListener('click', function(){
    if (!username) {
      window.location = '../Login/Login.php';
    } else {
      togglePopup();
    }
  });
  var form = document.getElementById('mealplan');
  form.addEventListener('submit', function(){
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    console.log("form submitted");
    xhr.open('POST', 'BackEnd/meal.php', true);
    xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
              if (xhr.status === 200) {
                  // Handle success response
                  console.log('meal request successful');
                  window.location.reload();
              } else {
                  // Handle error response
                  console.error('Error adding recipe to meal plan:', xhr.statusText);
              }
          }
      };
      xhr.send(formData);
  });

  // add login check on click functions
  saveButton.addEventListener('click', function() {
    if (!username) {
      window.location = '../Login/Login.php';
    }
    // Retrieve user_id and recipe_id from data attributes
    var userId = saveButton.querySelector('input[name="user_id"]').value;
    var recipeId = saveButton.querySelector('input[name="recipe_id"]').value;
    console.log(userId);
    console.log(recipeId);
    
    // AJAX request to save the recipe
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'BackEnd/saves.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Handle success response
                console.log('save requestsuccessful');
                // reload the page
                window.location.reload();
            } else {
                // Handle error response
                console.error('Error saving recipe:', xhr.statusText);
            }
        }
    };
    // Prepare data to be sent in the request body
    var data = 'recipe_id=' + encodeURIComponent(recipeId);
    // Send the request
    xhr.send(data);
  });
});


function togglePopup() { 
  const overlay = document.getElementById('popupOverlay'); 
  overlay.classList.toggle('show'); 
} 


function add_comment(){
  var username = getCookie('username');
  if (!username) {
    window.location = '../Login/Login.php';
  }
  else{

    const comment = document.getElementById("comment_input").value;
    let data = {"content": comment}

    fetch("BackEnd/comment_button_script.php", {
      "method": "POST",
      "headers": {
        "Content-Type": "application/json; charset=utf-8"
      },
      "body": JSON.stringify(data)
    }).then(function(response){
      return response.text();
    }).then(function(data){
      data = JSON.parse(data);
      if (data["success"]) {
        location.reload();
      }
      else{
        alert("Comment cannot be blank.");
      }
    })
  }
}