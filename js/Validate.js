function emailValid(email)
{
	return ((email.match(/^[a-z][a-z0-9_.-]+@gmail.com/g))!=null);
}