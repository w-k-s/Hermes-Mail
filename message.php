<?php

require 'php/include/Imap.php';

session_start();
ob_start();


if(!isset($_SESSION['username'])
	&& !isset($_SESSION['password'])
	&& !isset($_SESSION['mailbox']))
{
	header('Location: login.php');
	die();
}

if(!isset($_GET['n']))
{
	header('Location inbox.php');
	die();
}

$username = $_SESSION['username'];
$password = $_SESSION['password'];
$imap_server = "ssl://imap.gmail.com";
$imap_port = 993;
$msg_num = $_GET['n'];
$mailbox = $_SESSION['mailbox'];
$num_msgs = $_SESSION['num_msgs'];

$imap = new Imap($imap_server,$imap_port);
if(!$imap->login($username,$password))
{
	header('Location: login.php');
	die();
}

$body = $imap->get_message_body($imap::MAILBOX_INBOX,$msg_num);

$i = $num_msgs - $msg_num;
$feedback['from'] = $mailbox[$i][$imap::FIELD_FROM];
$feedback['to'] = $mailbox[$i][$imap::FIELD_TO];
$feedback['subject'] = $mailbox[$i][$imap::FIELD_SUBJECT];
$feedback['date'] = $mailbox[$i][$imap::FIELD_DATE];


$feedback['body'] = $body;

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
				<div><h3><?php if(isset($username)) echo $username?></h3></div>
				<div><a href="login.html">Log-Out</a></div>
			</div>
		</div>
		<div id="navigation">
			<ul id="nav">
				<li><a href="inbox.php">Inbox</a></li>
				<li><a href="inbox.php">Sent Mail</a></li>
				<li><a href="inbox.php">Drafts</a></li>
				<li><a href="inbox.php">Deleted Mail</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="buttonPanel">
				<input type="button" class="button" value="Reply" id="btn_reply"/>
			</div>
			<div id="infoPanel">
				<table>
					<tr>
						<td><strong>From:</strong></td>
						<td id='td_from'><?php if(isset($feedback['from'])) echo $feedback['from']?></td>
					</tr>
					<tr>
						<td><strong>Subject:</strong></td>
						<td id='td_subject'><?php if(isset($feedback['subject'])) echo $feedback['subject']?></td>
					</tr>
					<tr>
						<td><strong>Date:</strong></td>
						<td ><?php if(isset($feedback['date'])) echo $feedback['date']?></td>
					</tr>
					<tr>
						<td><strong>To:</strong></td>
						<td><?php if(isset($feedback['to'])) echo $feedback['to']?></td>
					</tr>
				</table>
			</div>
			<div id="emailPanel">
				<div id="messagePanel">
					<?php if(isset($feedback['body'])) echo $feedback['body']?>
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
		<script type="text/javascript" src="js/Tools.js"></script>
		<script type="text/javascript" src="js/Message.js"></script>
	</body>
</html>