var replyButton = document.getElementById('btn_reply');
var from = document.getElementById('td_from');
var subject = document.getElementById('td_subject');
var body = document.getElementById('messagePanel');

function replyMail(){


	
	mail_contents = new Array();
	mail_contents['reply_to'] = from.innerHTML;
	mail_contents['reply_subject'] = subject.innerHTML;
	mail_contents['reply_body'] = body.innerHTML;
	
	post("compose.php",mail_contents);
	
}

replyButton.addEventListener('click',replyMail,false);