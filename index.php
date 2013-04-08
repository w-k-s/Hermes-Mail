<?php

include 'include/Imap.php'; 

session_start();
ob_start();

//---------TEMPLATES-----------------//
$_login_template_uri = 'html/login.html';

//------TEMPLATE VARIABLES-----------//
$username = '';

//----------REDIRECTS----------------//
$_inbox_uri = 'inbox.php';


//if logged in redirect to inbox
if(isset($_SESSION['username']) &&
	isset($_SESSION['password']))
{
		header('Location: '.$_inbox_uri);
		die();
}

//if username and password submitted
if(isset($_POST['username']) && isset($_POST['password']))
{
	$username = htmlentities($_POST['username']);
	$password = htmlentities($_POST['password']);
	$result = '';

	try{
		//connect to imap server.
		$imap = new Imap('ssl://imap.gmail.com',993);
		
		if(!$imap->login($username,$password))
		{
			$result = $imap->error();
		}else{
			//load 200 headers.
			$total_num_mails = $imap->get_num_messages($imap::MAILBOX_INBOX);

			$load_size = $total_num_mails > 200 ? $total_num_mails - 200: 1;
			
			$inbox = $imap->get_headers($imap::MAILBOX_INBOX,"*",$load_size);

			//show mails from latest to oldest.
			$inbox = array_reverse($inbox);

			//cache inbox
			$_SESSION['num_msgs'] = $total_num_mails;
			$_SESSION['mailbox'] = $inbox;
			$_SESSION['username'] = $username;
			$_SESSION['password'] = $password;
			header('Location: '.$_inbox_uri);
			die();
		}
	}catch(Exception $e)
	{
		$result = $e->getMessage();	
	}

}

	//load template
	$login_template = file_get_contents($_login_template_uri);

	//template array
	$from = array('{{@result}}','{{@username}}');
	
	//variable array
	$to = array($result,$username);
	
	//insert template variables into template and return.
	echo str_replace($from, $to, $login_template);

?>