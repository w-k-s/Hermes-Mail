<?php

/*
*
*Smtp class to send emails.
*
*@author: Waqqas Sheikh
*based on: https://code.google.com/a/apache-extras.org/p/phpmailer/source/browse/trunk/class.smtp.php?
*/

class Smtp{
	const Port = 465;
	const TimeOut = 45;
	const LocalHost = '127.0.0.1';
	const ResponseSize = 4096;

	private $SmtpConnection = NULL;
	public $Log = array();
	private $Error = NULL;

	private $Connected = false;
	private $Authenticated = false;

	private $MailServer;
	private $MailPort;

	/**
	*connects to smtp server and issues helo command
	*@param string $MailServer ssl smtp server address 
	*@param string $MailPort ssl smtp mail port
	*@throws Exception if connection could not be established
	*/
	public function __construct($MailServer,$MailPort)
	{

		if($MailServer == NULL) $MailServer = self::LocalHost;
		if($MailPort == NULL) $MailPort = self::Port;

		$this->MailServer = $MailServer;
		$this->MailPort = $MailPort;

		if(!$this->Connect($this->MailServer,$this->MailPort) 
			&& !$this->Helo())
		{
			throw new Exception('Failure to connect to server');
		}
	}

	/**
	*Destructor - quits SMTP session and closes connection
	*/
	function __destruct()
	{
		if($this->Connected)
			$this->Disconnect();
		
	}

	/**
	*connects to Smtp server
	*@param string $SmtpServer ssl smtp server address
	*@param integer $SmtpPort ssl smtp port
	*@return boolean
	*/
	private function Connect($SmtpServer,$SmtpPort)
	{

		if($SmtpServer == NULL) $SmtpServer = self::LocalHost;
		if($SmtpPort == NULL) $SmtpPort = 25;

		$this->SmtpConnection = fsockopen($SmtpServer,$SmtpPort,$errno, $errstr,self::TimeOut);
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);

		$this->Log['Connect'] = $SmtpResponse;

		$this->Connected = !empty($this->SmtpConnection);
		if(!$this->Connected)
		{
			$this->Error = 
			array('Error' => 'Connection to server could not be established.',
				  'Code' => $this->ResponseCode($SmtpResponse),
				  'Messafe' => $this->ResponseMessage($SmtpResponse));
		}

		return $this->Connected;
	}

	/**
	*Issues helo command to smtp server
	*@return boolean
	*/
	private function Helo()
	{
		@fputs($this->SmtpConnection,"HELO $LocalHost\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);

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

	/**
	*Authenticates user
	*@param string $Username non-encrypted username e.g. bob@gmail.com
	*@param string $password non-encrypted mail password.
	*@return boolean
	*/
	public function Login($username,$password)
	{
		//check for connection (not sure if this is necessary)
		if(!$this->Connected)
		{
			$this->Error = 
				array('Error'=>'Not connected to server.');
			return false;
		}

		//check for authentication (again, not sure)
		if($this->Authenticated)
		{
			$this->Error = 
				array('Error'=>'Already logged in. Disconnect before retrying to login.');
			return false;
		}

		//issue authentication command.
		//wont work if ssl server and port wasn't used in connection
		@fputs($this->SmtpConnection,"AUTH LOGIN\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Login'] = $SmtpResponse;

		if(!$this->ResponseCode($SmtpResponse)=="334")
		{
			$this->Error = 
			array('Error'=>'User could not be authenticated.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
			return false;
		}

		//if response is 334, return base 64 encoded username
		@fputs($this->SmtpConnection,base64_encode($username)."\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Username'] = $SmtpResponse;

		if(!$this->ResponseCode($SmtpResponse)=="334")
		{
			$this->Error = 
			array('Error'=>'Invalid Username.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
			return false;
		}

		//if response is 334, return base 64 encoded password
		@fputs($this->SmtpConnection,base64_encode($password)."\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Password'] = $SmtpResponse;

		//if response 235, ok, return true
		if ($this->ResponseCode($SmtpResponse)=="235")
		{
			$this->Authenticated = true;
			return $this->Authenticated;
		}
		else
		{
			$this->Error = 
			array('Error'=>'Username or password is incorrect.',
				  'Code'=> $this->ResponseCode($SmtpResponse),
				  'Message'=>$this->ResponseMessage($SmtpResponse));
		}

		return false;
	}

	/**
	*Creates header for mail
	*@param string $from users email address
	*@param string $to receipants email address
	*@param string $subject mail subject
	*@return string
	*/
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

	/**
	*sends mail 
	*@param string $from users email address
	*@param string $to receipants email address
	*@param string $subject mail subject
	*@param string $message mail body
	*@return boolean
	*/
	public function SendMail($from,$to,$subject,$message)
	{
		//check for connection
		if(!$this->Connected)
		{
			$this->Error = 
				array('Error'=>'Not connected to server.');
			return false;
		}

		//check for authentication
		if(!$this->Authenticated)
		{
			$this->Error = 
				array('Error'=>'Must login before sending email.');
			return false;
		}

		//issue mail from command
		@fputs($this->SmtpConnection,"MAIL FROM:<$from>\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['From'] = $SmtpResponse;

		//if not ok, return false
		if($this->ResponseCode($SmtpResponse)!="250")
			return false;

		//issue receipant smtp command
		@fputs($this->SmtpConnection,"RCPT TO:<$to>\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['To'] = $SmtpResponse;

		//if not ok, return false
		if($this->ResponseCode($SmtpResponse) != "250")
			return false;

		//write message
		@fputs($this->SmtpConnection,"DATA\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Data'] = $SmtpResponse;

		//if not ok, return false
		if($this->ResponseCode($SmtpResponse) != "354")
			return false;

		//create header for mail
		$header = $this->CreateHeader($from,$to,$subject);

		//output message
		@fputs($this->SmtpConnection,"To: $to\r\nFrom: $from\r\nSubject: $subject\r\n$header\r\n\r\n$message\r\n.\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Message'] = $SmtpResponse;

		//if ok, return true
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

	/**
	*quits smtp and closes socket
	*@return boolean
	*/
	public function Disconnect()
	{
		if(!$this->Connected)
			return;	

		@fputs($this->SmtpConnection,"QUIT\r\n");
		$SmtpResponse = @fgets($this->SmtpConnection,self::ResponseSize);
		$this->Log['Quit'] = $SmtpResponse;

		@fclose($this->SmtpConnection);
		$this->Connected = false;
	
	}

	/**
	* returns response code from response
	*@param string $response response
	*@return integer
	*/
	private function ResponseCode($Response)
	{
		return substr($Response, 0,3);
	}

	/**
	* returns response message from response
	*@param string $response message
	*@return string
	*/	
	private function ResponseMessage($Response)
	{
		return substr($Response, 4);
	}

	/**
	* returns error array ('Error','Code','Message')
	*@return array()
	*/
	public function Error()
	{
		return $this->Error;
	}

}
?>