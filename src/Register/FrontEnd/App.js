'use strict';



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
  let email = document.getElementById('email').value;
  let username = document.getElementById('username').value;
  let password = document.getElementById('password').value;
  let password2 = document.getElementById('confirmPassword').value;

  // check that the passwords match
  console.log("Email: "+ email);
  console.log("Username: "+ username);
  if(password != password2){
    console.log("Passwords do not match");
  }else{
    console.log("Password: "+password);
  }

  // create an othat holds user inoputs
  let registerData = {
    Email: email,
    Username: username,
    Password: password
  };

  // form AJAX request
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../Register.php", true);
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

/*Actual validation function*/
function ValidatePassword() {
  /*Array of rules and the information target*/
  var rules = [{
      Pattern: "[A-Z]",
      Target: "UpperCase"
    },
    {
      Pattern: "[a-z]",
      Target: "LowerCase"
    },
    {
      Pattern: "[0-9]",
      Target: "Numbers"
    },
    {
      Pattern: "[!@#$%^&*(),.?\":{}|<>]",
      Target: "Symbols"
    }
  ];

  let pass1 = document.getElementById('password');
  let pass2 = document.getElementById('confirmPassword');
  if (pass2.value != "") {
    let defCheck = document.getElementById('defCheck');
    defCheck.value="1";
  }
  let errorMatch = document.getElementById('passErrorMatch');

  if (pass1.value != pass2.value && defCheck.value != "") {
    errorMatch.style.display="block";
  } else {
    errorMatch.style.display="none";
  }

  let errorLength = document.getElementById("passErrorLength");
  if (pass1.value.length < 6 || pass1.value.length > 12) {
    errorLength.style.display="block";
  } else {
    errorLength.style.display="none";
  }

  let errorSpecial = document.getElementById("passErrorSpecial");
  if (!(new RegExp(rules[3].Pattern).test(pass1.value))) {
    errorSpecial.style.display="block";
  } else {
    errorSpecial.style.display="none";
  }
}

$(document).ready(function() {
  $("#password").on('keyup', ValidatePassword)
});
$(document).ready(function() {
  $("#confirmPassword").on('keyup', ValidatePassword)
});