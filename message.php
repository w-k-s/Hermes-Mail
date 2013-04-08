<?php

require 'include/Imap.php';

session_start();
ob_start();

//----------TEMPLATES---------------//
$_message_template_uri = 'html/message.html';

//--------TEMPLATE VARIABLES--------//
$username = '';
$feedback = array();

//-----------REDIRECTS-------------//
$_inbox_uri = 'inbox.php';
$_login_uri = 'login.php';

//if not logged in or mailbox not cached
//redirect to login page.
if(!isset($_SESSION['username'])
	&& !isset($_SESSION['password'])
	&& !isset($_SESSION['mailbox']))
{
	header('Location: '.$_login_uri);
	die();
}else


//if number of message not set, return to inbox.
//TODO: (should display an error of some sort)
//Man, do you really need to talk so loud on the phone i'm pretty sure he can here u!!! :@
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

//connect with imap server
$imap = new Imap($imap_server,$imap_port);
if(!$imap->login($username,$password))
{
	header('Location: login.php');
	die();
}

//load message
$body = $imap->get_message_body($imap::MAILBOX_INBOX,$msg_num);

//get header information from cached inbox (instead of loading it again which would take some time)
$i = $num_msgs - $msg_num;
$feedback['from'] = $mailbox[$i][$imap::FIELD_FROM];
$feedback['to'] = $mailbox[$i][$imap::FIELD_TO];
$feedback['subject'] = $mailbox[$i][$imap::FIELD_SUBJECT];
$feedback['date'] = $mailbox[$i][$imap::FIELD_DATE];
$feedback['body'] = $body;

//load template
$message_template = file_get_contents($_message_template_uri);
$from = array('{{@username}}','{{@from}}','{{@to}}','{{@date}}','{{@subject}}','{{@body}}');
$to = array($username,$feedback['from'],$feedback['to'],$feedback['date'],$feedback['subject'],$feedback['body']);

//insert template variables into template and return
echo str_replace($from, $to, $message_template);
?>