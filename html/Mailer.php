<?php

require 'include/Imap.php';
require 'include/Smtp.php';

class Mailer
{
	const imap_server = "ssl://imap.gmail.com";
	const imap_port = 993;
	const smtp_server = "ssl://smtp.gmail.com";
	const smtp_port = 465;

	private $imap = NULL;
	private $smtp = NULL;
	private $username = "";
	private $password = "";

	private $logged_in = false;

	function __construct()
	{
		try{
			$this->imap = new Imap(self::imap_server,self::imap_port);
			$this->smtp = new Smtp(self::smtp_server,self::smtp_port);
		}catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	function login($username,$password)
	{
		if($username != NULL) $this->username = $username;
		if($password != NULL) $this->password = $password;

		//$this->imap = new Imap(self::imap_server,self::imap_port);
		if(!$this->imap->login($this->username,$this->password))
		{
			$result = array('success'=>0,'message'=>$this->imap->error());
			return $result;
		}

		$result = array('success'=>1,'message'=>'Login successful');
		return $result;
	}

	function send_mail($from,$to,$subject,$message)
	{
		
		if(!$this->smtp->SendMail($from,$to,$subject,$message))
		{
			$error = $this->smtp->Error();
			$result = array('success'=>false,'message'=>$error['Error']);
		}else
		{
			$result = array('success'=>true,'message'=>'Message Delivered');
		}

		return $result;
	}

	function is_logged_in()
	{
		return $this->logged_in;
	}

	function username()
	{
		return $this->username;
	}

	function __wakeup()
	{
		try{
			$this->imap = new Imap(self::imap_server,self::imap_port);
			$this->smtp = new Smtp(self::smtp_server,self::smtp_port);
		}catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}	
	}

	function __sleep()
	{
		
	}
}

?>