var emailField = document.getElementById('txt_email');
var passwordField = document.getElementById('txt_password');

function doValidation()
{
	email = emailField.value.trim();
	password = passwordField.value.trim();

	if(email== "" || password == "")
	{
		alert('You must provide your username and your password');
		return false;
	}

	if(!emailIsValid(email))
	{
		alert('The email is invalid.');
		return false;
	}

	return true;
}