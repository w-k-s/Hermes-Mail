<?php

require 'include/Imap.php';// or die('Failed to load IMAP files');

session_start();
ob_start();

//-----------REDIRECTS----------//
$_inbox_uri = 'inbox.php';
$_logout_uri = 'logout.php';

//check that the user is logged in and a message to delete has been selected.
//otherwise retur to inbox.
if(isset($_SESSION['username']) && isset($_SESSION['password']) && isset($_POST['delete_list']))
{

	$username = $_SESSION['username'];
	$password = $_SESSION['password'];

	//get message numbers to be deleted.
	$delete_list = explode(',',$_POST['delete_list']);

	try{
		//connect to imap server
		$imap = new Imap('ssl://imap.gmail.com',993);
		
		//authenticate user.
		if(!$imap->login($username,$password))
		{
			header('Location: '.$_inbox_uri.'?d=0');
		}else{
			//delete each message from delete list and then expunge
			foreach ($delete_list as $num)
				$imap->delete_mail($imap::MAILBOX_INBOX,$num);
			$imap->expunge();
			header('Location: '.$_inbox_uri);
		}
	}catch(Exception $e)
	{
		header('Location: '.$_inbox_uri.'?d=0');
	}

}else
	header('Location: '.$_inbox_uri);
?>