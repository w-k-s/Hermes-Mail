var sendButton = document.getElementById("btn_send");
var cancelButton = document.getElementById("btn_cancel");

var toField = document.getElementById("txt_to");
var ccField = document.getElementById("txt_cc");
var bccField = document.getElementById("txt_bcc");
var subjectField = document.getElementById("txt_subject");
var textarea = document.getElementById("txt_message");

var allEmails = new Array();
var invalidEmails = new Array();

sendButton.addEventListener("click",doValidation,false);
cancelButton.addEventListener("click",doCancel,false);

function doValidation()
{
	allEmails = [];
	invalidEmails = [];

	if(toFieldIsEmpty())
	{
		alert('You must provide a receipant email address');
		return false;
	}

	if(!allEmailsValid())
	{
		message = "The following emails are not valid:\n";
		for(var i=0; i< invalidEmails.length; i++)
			message += invalidEmails[i]+"\n";

		alert(message);

		return false;
	}

	if(!subjectAndBodyBlank())
	{
		alert("Message Sent!");
		return true;
	}

}

function toFieldIsEmpty()
{
	return (toField.value.trim() == "");
}

function allEmailsValid()
{
	getAllEmails();

	for(i = 0; i < allEmails.length; i++)
		if(!emailIsValid(allEmails[i]))
			invalidEmails.push(allEmails[i]);

	//if all emails are valid, invalid emails should be empty.
	return (invalidEmails.length == 0);
}

function getAllEmails()
{
	var receipants = new Array(toField,ccField,bccField);

	for(var i = 0; i<receipants.length; i++)
	{
		var emails = receipants[i].value.trim().split(",");
		for(var j = 0; j < emails.length; j++)
		{
			if(emails[j] != "")
				allEmails.push(emails[j].trim());
		}
	}
}

function subjectFieldBlank(){
	if(subjectField.value.trim()=="")
		return confirm("Are you sure you want to send this email without a subject?");
}

function subjectAndBodyBlank()
{
	subjectEmpty = (subjectField.value.trim() == "");
	messageEmpty = (textarea.value.trim() == '');

	if(!subjectEmpty && !messageEmpty)
		return false;

	else
	{
		message = "Are you sure you want to send this email without ";
		message += (subjectEmpty)? "a subject ":"";
		message += (subjectEmpty && messageEmpty)? "and ":"";
		message += (messageEmpty)? "a body": "";

		message += "?";

		return !confirm(message);
	}
}

function doCancel()
{
	if(confirm("Are you sure you want to cancel writing this message?"))
		location.href='inbox.html';
}