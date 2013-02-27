var emailField = document.getElementById('txt_email');
var passwordField = document.getElementById('txt_password');

function doValidation()
{
	okay = false;
	email = emailField.trim().value;
	password = passwordField.trim().value;

	if(email== "" || password == "")
	{
		alert('You must provide your username and your password');
	}

	if(!emailIsValid(email))
	{
		alert('The email is invalid.');
	}

	okay = true 
	return okay;
}