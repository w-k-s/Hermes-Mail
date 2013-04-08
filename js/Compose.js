//get buttons
var sendButton = document.getElementById("btn_send");
var cancelButton = document.getElementById("btn_cancel");

//add event handlers to buttons
sendButton.addEventListener("click",doValidation,false);
cancelButton.addEventListener("click",doCancel,false);

function doValidation()
{

	to = document.getElementById("txt_to").value;
	subject = document.getElementById("txt_subject").value;
	message = document.getElementById("txt_message").value;

	//receipant email must be provided
	if(toFieldIsEmpty())
	{
		alert('You must provide a receipant email address');
		return false;
	}

	//check email has valid format
	if(!emailValid(to))
	{
		error = "Receipant email is not valid:\n";
		error += to+"\n";

		alert(error);

		return false;
	}

	//give warning that subject is blank
	if(!subjectAndBodyBlank())
	{
		params = new Array();
		params["to"] = to;
		params["subject"] = subject;
		params["body"] = message;

		post("compose.php",params);
	}
}

function toFieldIsEmpty()
{
	return (to.trim() == "");
}


function subjectAndBodyBlank()
{
	subjectEmpty = (subject.trim() == "");
	messageEmpty = (message == "");

	//if subject and message written, return false
	if(!subjectEmpty && !messageEmpty)
		return false;

	//if subject and message not provided, confirm dialog
	else
	{
		error = "Are you sure you want to send this email without ";
		error += (subjectEmpty)? "a subject ":"";
		error += (subjectEmpty && messageEmpty)? "and ":"";
		error += (messageEmpty)? "a body": "";

		error += "?";

		return !confirm(error);
	}
}

function doCancel()
{
	if(confirm("Are you sure you want to cancel writing this message?"))
		location.href='inbox.php';
}