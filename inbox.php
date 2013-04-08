<?php

require 'include/Imap.php';

session_start();

//-------------TEMPLATES--------------//
$inbox_template_uri = 'html/inbox.html';

//-----------TEMPLATE VARIABLES-------//
$username = 'null';
$feedback = 'Inbox could not be loaded';
$dialog = '';

//-----------REDIRECTS---------------//
$login_uri = 'index.php';

//logged in and mailbox cached
if(isset($_SESSION['username']) 
	&& isset($_SESSION['password'])
	&& isset($_SESSION['num_msgs'])
	&& isset($_SESSION['mailbox']))
{
	//get cached total number of messages in inbox.
	$num_cached_msgs = $_SESSION['num_msgs'];
	
	//get cached inbox
	$inbox = $_SESSION['mailbox'];
	
	$username = $_SESSION['username'];
	$password = $_SESSION['password'];
	$imap_server = "ssl://imap.gmail.com";
	$imap_port = 993;
	
	//connect to imap server
	$imap = new Imap($imap_server,$imap_port);
	if(!$imap->login($username,$password))
	{
		header('Location: '.$login_uri);
		die();
	}

	//get latest total number of messages in inbox
	$num_msgs = $imap->get_num_messages($imap::MAILBOX_INBOX);

	//if latest num of messages is greater than cached num of messages
	//more mails have arrived
	if($num_msgs > $num_cached_msgs )
	{
		//calculate number of new messages to load
		$num_new_msgs = $num_msgs - $num_cached_msgs;

		//load headers of new messages
		$new_msgs = $imap->get_headers($imap::MAILBOX_INBOX,$num_msgs,($num_msgs - $num_new_msgs+1));
		
		//arrange new mails in chronoligically descending order
		$new_msgs = array_reverse($new_msgs);

		//combine with inbox
		$inbox = array_merge($new_msgs,$inbox);
		
		//update cache
		$_SESSION['mailbox'] = $inbox;
		$_SESSION['num_msgs'] = $num_msgs;
	}

	//if latest num of messages less than cached number of messages
	//user has deleted mails.
	if($num_msgs < $num_cached_msgs)
	{
		//ge total number of mails
		$total_num_mails = $imap->get_num_messages($imap::MAILBOX_INBOX);

		//load no more than 200 headers
			$load_size = $total_num_mails > 200 ? $total_num_mails - 200: 1;
			$inbox = $imap->get_headers($imap::MAILBOX_INBOX,"*",$load_size);

			//arrange from latest to oldest
			$inbox = array_reverse($inbox);

			//update cache
			$_SESSION['num_msgs'] = $total_num_mails;
			$_SESSION['mailbox'] = $inbox;
	}

	//if inbox loaded
	if(is_array($inbox))
	{
		//organise headers into inbox table elemnt
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

			$feedback .= "<tr class='message' number='$number'>";
			$feedback .= "<td clickable='false'><input type='checkbox'/></td>";
			$feedback .= "<td clickable='true'>$from</td>";
			$feedback .= "<td class='td_subject' clickable='true'>$subject</td>";
			$feedback .= "<td clickable='true'>$date</td>";
			$feedback .= "</tr>";
		}
		$feedback .= '</table>';
	}

	if(isset($_GET['d']))
	{
		$delete_status = $_GET['d'];
		switch ($delete_status) {
			case '0':
				$dialog = 'alert("Messages could not be deleted.");';
				break;
			
			case '1':
				//dont show a dialog. That'd be annoying.
				break;
			default:
				# code...
				break;
		}
	}
}
else
	//avoid circular loop by asking the user to log out.
	die('You could not be signed in. Please <a href="logout.php">Log-out</a> and try again. Sorry :( ');

//load inbox template
$inbox_template = file_get_contents($inbox_template_uri);
$from = array('{{@username}}','{{@inbox}}','{{@dialog}}');
$to = array($username,$feedback,$dialog);

//insert template variables into template and return.
echo str_replace($from, $to, $inbox_template);

?>
