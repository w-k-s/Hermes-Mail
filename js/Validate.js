function emailIsValid(email)
{
	return ((email.match(/^[a-z][a-z0-9_.-]+@[a-z]+/g))!=null);
}