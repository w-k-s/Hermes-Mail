
//should contain @ surrounded by alphanumeric chars
function emailValid(email)
{
	return ((email.match(/<[a-z][a-z0-9_.-]+@[a-z][a-z0-9_.-]+>/g))!=null);
}

//shoudl be a valid gmail username
function loginValid(email)
{
	return ((email.match(/@gmail.com/g))!=null);	
}