var emailField = document.getElementById('txt_email');
var passwordField = document.getElementById('txt_password');
var messageField = document.getElementById('p_message');

function doValidation()
{
	email = emailField.value.trim();
	password = passwordField.value.trim();

	//both details must be provided.
	if(email== "" || password == "")
	{
		messageField.innerHTML = '<span style="color: #b22222">You must provide your username and your password</span>';
		return false;
	}

	//email must be formatted correct and must be Gmail.
	if(!loginValid(email))
	{
		messageField.innerHTML = '<span style="color: #b22222">Invalid email. Please make sure you\'re using your GMail account.</span>';
		return false;
	}

	return true;
}