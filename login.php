<?php

include 'php/include/Imap.php'; 

session_start();
ob_start();

if(isset($_SESSION['username']) &&
	isset($_SESSION['password']))
		@header('Location: inbox.php');

if(isset($_POST['username']) && isset($_POST['password']))
{
	//ENCRYPT
	$username = htmlentities($_POST['username']);
	$password = htmlentities($_POST['password']);
	$feedback;

	unset($feedback);

	try{
		$imap = new Imap('ssl://imap.gmail.com',993);
		
		if(!$imap->login($username,$password))
		{
			$feedback = '<span style="color: #b22222">'.$imap->error().'</span>';
		}else{
			
			$total_num_mails = $imap->get_num_messages($imap::MAILBOX_INBOX);


			$load_size = $total_num_mails > 200 ? $total_num_mails - 200: 1;

			$inbox = $imap->get_headers($imap::MAILBOX_INBOX,"*",$load_size);

			$inbox = array_reverse($inbox);

			//ENCRYPT
			$_SESSION['num_msgs'] = $total_num_mails;
			$_SESSION['mailbox'] = $inbox;
			$_SESSION['username'] = $username;
			$_SESSION['password'] = $password;
			header('Location: inbox.php');
		}
	}catch(Exception $e)
	{
		$feedback = '<span style="color: #b22222">'.$e->getMessage().'</span>';	
	}	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mail</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="css/Core.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Login.css" type="text/css"></link>
		
	</head>
	<body>
		<div id="wrapper">
			<div id="header">
			</div>
			<div id="content">
				<div id="loginPanel">
					<div id="logoPanel">
						<img id="logo" src="res/logo.png" alt="logo"></img>
						<h1>HERMES</h1>
					</div>
					<form method="post" action="login.php" onsubmit="return doValidation();">
						<p><strong>E-MAIL:</strong></p>
						<p><input type="text" id="txt_email" class="field" name="username" size="30" <?php if(isset($username)) echo 'value="'.$username.'"'; else echo 'value="@gmail.com"'?>/></p>
						<p><strong>PASSWORD:</strong></p>
						<p><input type="password" id="txt_password" class="field" name="password" size="30"/></p>
						<p id='p_message' class='message'>
							<?php if(isset($feedback)) echo $feedback ?>
						</p>
						<p><input type="submit" class="button" id="btn_login" value="Log-in"/></p>
					</form>
				</div>
			</div>
			<div id="footer">
				<p id="overview"><a href="overview.html">Overview</a></p>
				<br/><br/>
				<p>Hermes Mail &copy; Asim, Essam, Tariq, Waqqas.</p>
			</div>
		</div>
		<script type="text/javascript" src="js/Validate.js"></script>
		<script type="text/javascript" src="js/Login.js"></script>
	</body>
</html>