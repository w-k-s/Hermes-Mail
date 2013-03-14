<?php

/*
* Author : Waqqas Sheikh
* Date: 12-March-2013
* Description:
*  - Login Authentication
*  - Sending mail.
*/

class Smtp{
	const SmtpServer = 'ssl://smtp.gmail.com';
	const SmtpPort = 465;
	const TimeOut = 45;
	const NewLine = "\r\n";
	const LocalHost = '127.0.0.1';
	const ResponseSize = 4096;

	private $SmtpConnection = NULL;
	public $Log = array();
	private $Error = NULL;

	private $Connected = false;

	//Connect with Smtp 
	public function Connect()
	{
		if(!$this->Connected)
		{
			$this->SmtpConnection = fsockopen(self::SmtpServer,self::SmtpPort,$errno, $errstr,self::TimeOut);
			$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);

			$this->Log['Connect'] = $SmtpResponse;

			$this->Connected = !empty($this->SmtpConnection);
			if(!$this->Connected)
			{
				$this->Error = 
				array('Error' => 'Connection to server could not be established.',
					  'Code' => $this->ResponseCode($SmtpResponse),
					  'Messafe' => $this->ResponseMessage($SmtpResponse));
			}
		}		

		return $this->Connected;
	}

	public function Helo()
	{
		if(!$this->Connected)
		{
			$this->Error = 
				array('Error'=>'Not connected to server.');
			return false;
		}

		fputs($this->SmtpConnection,"HELO $LocalHost\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);

		$this->Log['Helo'] = $SmtpResponse;

		if($this->ResponseCode($SmtpResponse)=="250")
			return true;
		else
		{
			$this->Error = 
			array('Error' => 'The Server is ignoring me!',
				  'Code' => $this->ResponseCode($SmtpResponse),
				  'Message'=> $this->ResponseMessage($SmtpResponse));
		}

		return false;
	}

	public function Login($username,$password)
	{
		if(!$this->Connected)
		{
			$this->Error = 
				array('Error'=>'Not connected to server.');
			return false;
		}

		fputs($this->SmtpConnection,"AUTH LOGIN\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Login'] = $SmtpResponse;

		if(!$this->ResponseCode($SmtpResponse)=="334")
		{
			$this->Error = 
			array('Error'=>'User could not be authenticated.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
			return false;
		}

		fputs($this->SmtpConnection,base64_encode($username)."\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Username'] = $SmtpResponse;

		if(!$this->ResponseCode($SmtpResponse)=="334")
		{
			$this->Error = 
			array('Error'=>'Invalid Username.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
			return false;
		}

		fputs($this->SmtpConnection,base64_encode($password)."\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Password'] = $SmtpResponse;

		if ($this->ResponseCode($SmtpResponse)=="235")
			return true;
		else
		{
			$this->Error = 
			array('Error'=>'Username or password is incorrect.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
		}

		return false;
	}

	private function CreateHeader($from,$to,$subject)
	{
		$header = 'MIME-Version: 1.0\r\n';
		$header .= 'From: '.$Username.'\r\n';
		$header .= 'To: '.$to.'\r\n';
		$header .= 'Subject: '.$subject.'\r\n';
		$header .= 'Date: '.date('l j F, Y - h:i A').'\r\n';
		$header .= 'Content-Type: '.'text/html; charset="UTF-8"'.'\r\n';

		return $header;
	}

	public function SendMail($from,$to,$subject,$message)
	{
		if(!$this->Connected)
		{
			$this->Error = 
				array('Error'=>'Not connected to server.');
			return false;
		}

		fputs($this->SmtpConnection,"MAIL FROM:<$from>\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['From'] = $SmtpResponse;

		if($this->ResponseCode($SmtpResponse)!="250")
			return false;

		fputs($this->SmtpConnection,"RCPT TO:<$to>\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['To'] = $SmtpResponse;

		if($this->ResponseCode($SmtpResponse) != "250")
			return false;

		fputs($this->SmtpConnection,"DATA\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Data'] = $SmtpResponse;

		if($this->ResponseCode($SmtpResponse) != "354")
			return false;

		$header = $this->CreateHeader($from,$to,$subject);

		fputs($this->SmtpConnection,"To: $to\r\nFrom: $from\r\nSubject: $subject\r\n$header\r\n\r\n$message\r\n.\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Message'] = $SmtpResponse;

		if($this->ResponseCode($SmtpResponse)=="250")
			return true;
		else
		{
			$this->Error = 
			array('Error'=>'Email could not be sent at this time.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
		}

		return false;
	}

	public function Disconnect()
	{
		if(!$this->Connected)
			return;	

		fputs($this->SmtpConnection,"QUIT\r\n");
		$SmtpResponse = fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Quit'] = $SmtpResponse;

		fclose($this->SmtpConnection);
		$this->Connected = false;
	
	}

	private function ResponseCode($Response)
	{
		return substr($Response, 0,3);
	}

	private function ResponseMessage($Response)
	{
		return substr($Response, 4);
	}

	public function Error()
	{
		return $this->Error;
	}

}

$smtp = new Smtp();
$smtp->Connect().'<br/>';
echo $smtp->Log['Connect'].'<br/>';
$smtp->Helo().'<br/>';
echo $smtp->Log['Helo'].'<br/>';
$smtp->Login("waqqas.abdulkareem@gmail.com","Silmarilis");
echo $smtp->Log['Password'].'<br/>';

$from = "waqqas.abdulkareem@gmail.com";
$to = "developer.wks@gmail.com";
$subject = "shit";
$message = "let's see";

echo $smtp->SendMail($from,$to,$subject,$message);
echo 'from: '.$smtp->Log['From'].'<br/>';
echo 'to: '.$smtp->Log['To'].'<br/>';
echo 'data: '.$smtp->Log['Data'].'<br/>';
echo 'msg: '.$smtp->Log['Message'].'<br/>';



?>