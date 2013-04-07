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
		$_SESSION['num_msgs'] = $num_msgs;
	}
	if($num_msgs < $num_cached_msgs)
	{
		$total_num_mails = $imap->get_num_messages($imap::MAILBOX_INBOX);
			$load_size = $total_num_mails > 200 ? $total_num_mails - 200: 1;
			$inbox = $imap->get_headers($imap::MAILBOX_INBOX,"*",$load_size);

			$inbox = array_reverse($inbox);

			//ENCRYPT
			$_SESSION['num_msgs'] = $total_num_mails;
			$_SESSION['mailbox'] = $inbox;
	}

	if(is_array($inbox))
	{

		$feedback = "<table id='table_inbox'>";

		for($i=0; $i<count($inbox); $i++)
		{

			$number = $inbox[$i][$imap::FIELD_NUMBER];
			$from = $inbox[$i][$imap::FIELD_FROM];
			$from = substr($from, 0,strpos($from, '&lt;'));
			$subject = $inbox[$i][$imap::FIELD_SUBJECT];
			$date = $inbox[$i][$imap::FIELD_DATE];
			$flags = $inbox[$i][$imap::FIELD_FLAG];
			//$new = strpos($flags, $imap::FLAG_)

			$feedback .= "<tr number='$number'>";
			$feedback .= "<td clickable='false'><input type='checkbox'/></td>";
			$feedback .= "<td clickable='true'>$from</td>";
			$feedback .= "<td class='td_subject' clickable='true'>$subject</td>";
			$feedback .= "<td clickable='true'>$date</td>";
			$feedback .= "</tr>";
		}
		$feedback .= '</table>';
	}

}else
	die('There was a problem signing you in.<br/>Please <a href="php/logout.php">logout</a> and try again.<br/> Sorry :(');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Mail</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" href="css/Core.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Frame.css" type="text/css"></link>
		<link rel="stylesheet" href="css/Inbox.css" type="text/css"></link>
		<script type='text/javascript'>
			<?php if(isset($_GET['d'])){ if($_GET['d']==0) echo 'alert("Messages could not be deleted at this time.");';} ?>
		</script>
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
				<input type="button" class="button" id="btn_delete" value="Delete" />
				<input type="button" class="button" value="Refresh" onclick="location.reload()"/>
				<input type="text" id="searchfield" value="Search"/>
			</div>
			<div id="emailPanel">
				<?php if(isset($feedback)) echo $feedback;?>
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
		<script type="text/javascript" src="js/Tools.js"></script>
		<script type="text/javascript" src="js/Inbox.js"></script>
		<script type='text/javascript' src='js/Search.js'></script>
	</body>
</html>