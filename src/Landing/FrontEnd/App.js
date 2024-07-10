'use strict';

const reactElem = React.createElement;

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