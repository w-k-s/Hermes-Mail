var emailField = document.getElementById('txt_email');
var passwordField = document.getElementById('txt_password');
var messageField = document.getElementById('p_message');

function doValidation()
{
	email = emailField.value.trim();
	password = passwordField.value.trim();

	if(email== "" || password == "")
	{
		messageField.innerHTML = '<span style="color: #b22222">You must provide your username and your password</span>';
		return false;
	}

	if(!emailIsValid(email))
	{
		messageField.innerHTML = '<span style="color: #b22222">Invalid email. Please make sure you\'re using your GMail account.</span>';
		return false;
	}

	return true;
}