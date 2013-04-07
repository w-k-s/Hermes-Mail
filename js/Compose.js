var sendButton = document.getElementById("btn_send");
var cancelButton = document.getElementById("btn_cancel");

sendButton.addEventListener("click",doValidation,false);
cancelButton.addEventListener("click",doCancel,false);

function doValidation()
{

	to = document.getElementById("txt_to").value;
	subject = document.getElementById("txt_subject").value;
	message = document.getElementById("txt_message").value;

	if(toFieldIsEmpty())
	{
		alert('You must provide a receipant email address');
		return false;
	}

	if(!emailValid(to))
	{
		error = "Receipant email is not valid:\n";
		error += to+"\n";

		alert(error);

		return false;
	}

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

	//default text in editor is <br>
	messageEmpty = (message == "<br>");


	if(!subjectEmpty && !messageEmpty)
		return false;

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
		location.href='inbox.html';
}