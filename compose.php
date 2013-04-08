<?php
require 'include/Smtp.php';
session_start();
ob_start();

//-----------TEMPLATES-----------//
$compose_template_uri = 'html/compose.html';

//--------TEMPLATE VARIABLES-----//
$username = '';
$to = '';
$subject = '';
$body = '';
$dialog = '';

//-----------REDIRECTS-----------//
$login_uri = 'index.php';

//if not logged in, send back to login page/
if(!isset($_SESSION['username']) ||
	!isset($_SESSION['password']))
{
		header('Location: '.$login_uri );
		die();
}else
{
	//otherwise load session variables
	$username = $_SESSION['username'];
	$password = $_SESSION['password'];
}

//If new mail has been submitted.
if(isset($_POST['to']) 
	&& isset($_POST['subject']) 
	&& isset($_POST['body']))
{
	
	$to = $_POST['to'];
	$subject = $_POST['subject'];
	$body = $_POST['body'];
	//connect with smtp server.
	try{
		$smtp = new Smtp("ssl://smtp.gmail.com","465");
		if(!$smtp->Login($username,$password))
		{
			$status = 'Message could not be sent because you are not signed-in';
		}
		
		//send mail
		if(!$smtp->SendMail($username,$to,$subject,$body))
		{
			$error = $smtp->Error();
			$status = $error['Error'];
			die();
		}else
			$status = 'Message sent!';

	}catch(Exception $e){
		$status = $e->getMessage();
	}

	$dialog = 'alert("'.$status.'");';
}

//if mail is being replied to, 
//insert original mails content into respective fields
if(isset($_POST['reply_to'])
	&& isset($_POST['reply_subject'])
	&& isset($_POST['reply_body']))
{
	$to = html_entity_decode($_POST['reply_to']);
	$subject = htmlentities($_POST['reply_subject']);
	$body = htmlentities($_POST['reply_body']);
}

//load template
$compose_template = file_get_contents($compose_template_uri);
$from = array('{{@username}}','{{@to}}','{{@subject}}','{{@body}}','{{@dialog}}');
$to = array($username,$to,$subject,$body,$dialog);

//insert template variables into template and return.
echo str_replace($from, $to, $compose_template);


?>
