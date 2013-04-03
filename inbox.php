<?php

require 'php/include/Imap.php';

session_start();


if(isset($_SESSION['username']) 
	&& isset($_SESSION['password'])
	&& isset($_SESSION['num_msgs'])
	&& isset($_SESSION['mailbox']))
{
	$num_cached_msgs = $_SESSION['num_msgs'];
	$inbox = $_SESSION['mailbox'];
	$username = $_SESSION['username'];
	$password = $_SESSION['password'];
	$imap_server = "ssl://imap.gmail.com";
	$imap_port = 993;
	
	$imap = new Imap($imap_server,$imap_port);
	if(!$imap->login($username,$password))
	{
		header('Location: login.php');
		die();
	}

	$num_msgs = $imap->get_num_messages($imap::MAILBOX_INBOX);
	if($num_msgs > $num_cached_msgs )
	{
		$num_new_msgs = $num_msgs - $num_cached_msgs;

		$new_msgs = $imap->get_headers($imap::MAILBOX_INBOX,$num_msgs,($num_msgs - $num_new_msgs+1));
		$new_msgs = array_reverse($new_msgs);
		$inbox = array_merge($new_msgs,$inbox);
		$_SESSION['mailbox'] = $inbox;
	}

	if(is_array($inbox))
	{

		$feedback = "<table id='table_inbox'>";

		for($i=0; $i<count($inbox); $i++)
		{

			$number = $inbox[$i][$imap::FIELD_NUMBER];
			$from = $inbox[$i][$imap::FIELD_FROM];
			$subject = $inbox[$i][$imap::FIELD_SUBJECT];
			$date = $inbox[$i][$imap::FIELD_DATE];
			$flags = $inbox[$i][$imap::FIELD_FLAG];
			//$new = strpos($flags, $imap::FLAG_)

			$feedback .= "<tr number='$number'>";
			$feedback .= "<td><input type='checkbox'/></td>";
			$feedback .= "<td>$from</td>";
			$feedback .= "<td class='td_subject'>$subject</td>";
			$feedback .= "<td>$date</td>";
			$feedback .= "</tr>";
		}
		$feedback .= '</table>';
	}
	/*
	}else{
		$feedback = 'Inbox could not be loaded.';
	}*/

}else
	header('Location: login.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mail</title>
		<link rel="shortcut icon" type="image/x-icon" href="res/favicon.ico"></link>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="css/Core.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Frame.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Inbox.css" type="text/css"></link>
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
				<div><h3><?php if(isset($username)) echo $username ?></h3></div>
				<div><a href="php/logout.php">Log-Out</a></div>
			</div>
		</div>
		<div id="navigation">
			<ul id="nav">
				<li><a href="inbox.php" style="font-weight: bold;">Inbox</a></li>
				<li><a href="inbox.php">Sent Mail</a></li>
				<li><a href="inbox.php">Drafts</a></li>
				<li><a href="inbox.php">Deleted Mail</a></li>
			</ul>
		</div>
		<div id="content">
			<div id="buttonPanel">
				<input type="button" class="button" value="Compose" onclick="location.href='compose.php'"/>
				<input type="button" class="button" id="delete" value="Delete" />
				<input type="button" class="button" value="Refresh" onclick="location.reload()"/>
				<input type="text" id="searchfield" value="Search"/>
			</div>
			<div id="emailPanel">
				<?php if(isset($feedback)) echo $feedback;?>
				<!--<table id="table_inbox">
						<tr>
							<td>type="checkbox" /></td>
							<td>Sender McSender</td>
							<td class="td_subject"><a href="message.php?num=1">I've sent you this email</a></td>
							<td>26 Feb 2013 20:03</td>
						</tr>
						<tr>
							<td><input onmouseover="link = false;" type="checkbox" /></td>
							<td>Sender McSender</td>
							<td class="td_subject">I've sent you this email</td>
							<td>26 Feb 2013 20:03</td>
						</tr>
				</table>-->
			</div>
			<div id="footer">
			 <p>
    			<a href="http://validator.w3.org/check?uri=referer"><img
      			src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
      			<a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="http://jigsaw.w3.org/css-validator/images/vcss-blue" alt="Valid CSS!" /></a>
			</p>
			</div>
		</div>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript" src="js/Inbox.js"></script>
		<script type='text/javascript' src='js/Search.js'></script>
	</body>
</html>