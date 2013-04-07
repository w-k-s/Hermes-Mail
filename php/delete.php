<?php

require 'include/Imap.php';// or die('Failed to load IMAP files');

session_start();
ob_start();

$rdr_inbox = '../inbox.php';
$rdr_logout = 'logout.php';

if(isset($_SESSION['username']) && isset($_SESSION['password']) && isset($_POST['delete_list']))
{

	$username = htmlentities($_SESSION['username']);
	$password = htmlentities($_SESSION['password']);
	$delete_list = explode(',',$_POST['delete_list']);

	try{
		$imap = new Imap('ssl://imap.gmail.com',993);
		
		if(!$imap->login($username,$password))
		{
			header('Location: '.$rdr_inbox.'?d=0');
		}else{
			foreach ($delete_list as $num)
				$imap->delete_mail($imap::MAILBOX_INBOX,$num);
			$imap->expunge();
			header('Location: '.$rdr_inbox);
		}
	}catch(Exception $e)
	{
		header('Location: '.$rdr_inbox.'?d=0');
	}

}else
	die('There was a problem signing you in.<br/>Please <a href="'.$rdr_logout.'">logout</a> and try again.<br/> Sorry :(');
?>