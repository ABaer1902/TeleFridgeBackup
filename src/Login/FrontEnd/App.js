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

  // console.log("Email: "+ email);
  // console.log("Username: "+ username);
  if(password != password2){
    console.log("Passwords do not match");
  }else{
    // console.log("Password: "+password);
  }

}



/*
// connects react component with div
//  explore button
const registerDom = document.querySelector('#registerButton');
const registerRoot = ReactDOM.createRoot(registerDom);
registerRoot.render(reactElem(RegisterButton));
*/