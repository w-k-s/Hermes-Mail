<?php
require 'php/include/Smtp.php';
session_start();
ob_start();

if(!isset($_SESSION['username']) ||
	!isset($_SESSION['password']))
		header('Location: http:///localhost/Network Applications/login.php');

if(isset($_POST['to']) 
	&& isset($_POST['subject']) 
	&& isset($_POST['body']))
{
	
	$to = $_POST['to'];
	$username = $_SESSION['username'];
	$password = $_SESSION['password'];
	$subject = $_POST['subject'];
	$body = $_POST['body'];

	try{
		$smtp = new Smtp("ssl://smtp.gmail.com","465");
		if(!$smtp->Login($username,$password))
		{
			//ENCRYPT
			header('Location: login.php?status=timeout');
		}
		
		if(!$smtp->SendMail($username,$to,$subject,$body))
		{
			$error = $smtp->Error();
			$status = $error['Error'];
		}else
			$status = 'Message sent!';

	}catch(Exception $e){
		$status = $e->getMessage();
	}

}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mail</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="css/Core.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Frame.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Message.css" type="text/css"></link>
	</head>
	<body>
		<div id="header">
			<div id="title">
				<table>
					<tr>
						<td><img src="res/logo_white.png" alt="logo" width="40" height="40"/></td>
						<td><h1>Hermes</h1></td>
					</tr>
				</table>
			</div>
			<div id="userPanel" >
				<div><h3><?php echo $_SESSION['username'] ?></h3></div>
				<div><a href="php/logout.php">Log-Out</a></div>
			</div>
		</div>
		<div id="navigation">
			<ul id="nav">
				<li><a href="inbox.html">Inbox</a></li>
				<li><a href="inbox.html">Sent Mail</a></li>
				<li><a href="inbox.html">Drafts</a></li>
				<li><a href="inbox.html">Deleted Mail</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="buttonPanel">
				<input type="button" id="btn_send" class="button" value="Send"/>
				<input type="button" id="btn_cancel" class="button" value="Cancel"/>
			</div>
			<div id="infoPanel" style="height: 10%">
				<table>
					<tr>
						<td><strong>To:</strong></td>
						<td><input type="text" id="txt_to" class="composefield" <?php if(isset($to)) echo 'value='.$to.''?> /></td>
					</tr>
					<!--<tr>
						<td><strong>Cc:</strong></td>
						<td><input type="text" id="txt_cc" class="composefield" size="100"/></td>
					</tr>
					<tr>
						<td><strong>Bcc:</strong></td>
						<td><input type="text" id="txt_bcc" class="composefield" size="100"/></td>
					</tr>-->
					<tr>
						<td><strong>Subject:</strong></td>
						<td><input type="text" id="txt_subject" class="composefield" <?php if(isset($subject)) echo 'value='.$subject.'' ?> /></td>
					</tr>
				</table>
			</div>
			<div id="emailPanel">
				<div id="holder">
					<textarea id="txt_message" cols="10" rows="10"><?php if(isset($body)) echo "$body"?></textarea>
				</div>
			</div>
			<div id="footer">
				 <p>
					<a href="http://validator.w3.org/check?uri=referer"><img
		  			src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
		  			<a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" /></a>
				</p>
			</div>
		</div>
		<script type="text/javascript" src="js/Validate.js"></script>
		<script type="text/javascript" src="js/Compose.js"></script>
		<?php if(isset($status)) echo '<script type="text/javascript">alert("'.$status.'");</script>'?>
	</body>
</html>