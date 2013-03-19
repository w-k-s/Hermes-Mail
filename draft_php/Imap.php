<?php

class Imap{
	
	const ResponseSize = 4096;
	const LocalHost = '127.0.0.1';
	const CRLF = "\r\n";
	const OK = "OK";
	const BAD = "BAD";
	const NO = "NO";

	private $_connection = NULL;
	private $_number = 0;
	private $_instructionNumber;

	private $_connected = false;
	private $_authenticated = false;
	
	public $error = array();

	function Connect($aImapServer, $aImapPort)
	{
		if(!$this->_connected)
		{
			if($aImapServer == NULL) $aImapServer = self::LocalHost;
			if($aImapPort == NULL) $aImapPort = 993;

			$this->_connection = fsockopen($aImapServer,$aImapPort);
		
			if(empty($this->_connection))
				$this->error = array('error' => 'Connection to server could not be established');
				
			
			else $this->_connected = true;		
		}
		
		return $this->_connected;
	}

	function Login($aUsername, $aPassword)
	{
		if(!$this->_connected)
		{
			$this->error = array('error' => 'Not connected to the server.');
			return false;
		}

		if(!$this->_authenticated)
		{
			$number = $this->InstructionNumber();
			fputs($this->_connection,"$number LOGIN $aUsername $aPassword".self::CRLF);
			$response = $this->Response($number);

			switch ($response['code']) {
				case self::OK:
					$this->_authenticated = true;
					break;
				
				case self::NO:
					$this->_authenticated = false;
					$this->error = array('error'=>'Invalid username or password.');
					break;

				case self::BAD:
					$this->_authenticated = false;
					$this->error = array('error'=>'BAD! You shouldn\'t see this.');
				break;

				default:
					$this->_authenticated = false;
					$this->error = array('error'=>'default.');
				break;
			}
		}

		return $this->_authenticated;
	}

	function Header($aMailbox, $aMessageId)
	{
		if(!$this->_connected)
		{
			$this->error = array('erorr'=>'Not connected to server.');
			return false;
		}

		if(!$this->_authenticated)
		{
			$this->error = array('error' => 'Not signed in.');
			return false;
		}

		if(!$this->SelectMailbox($aMailbox))
			return false;

		//select mail

		switch ($response['code']) 
		{
			case self::OK:
				$preg_match("/To:(.*?)<br\/>From:(.*?)<br\/>Subject:(.*?)<br\/>/i",$response['response'],$matches);
				return array('to'=>$matches[1],'from'=>$matches[2],'subject'=>$matches[3]);
				break;
				
			case self::NO:
				$this->error = array('error'=>'This folder does not exist.');
				return false;

			case self::BAD:
				$this->error = array('error'=>'BAD! You shouldn\'t see this.');
				return false;

			default:
				$this->error = array('error'=>'Unrecognised response code');
				return false;
		}


	}

	function Message($aMailbox, $aMessageId)
	{

	}

	private function Response($aInstructionNumber)
	{
		$end_of_response = false;

		while (!$end_of_response)
		{
			$line = fgets($this->_connection,self::ResponseSize);
			$response .= $line.'<br/>';

			if(preg_match("/$aInstructionNumber (OK|NO|BAD)/", $response,$responseCode))
				$end_of_response = true;
		}
		
		return array('code' => $responseCode[1],
			'response'=>$response);
	} 

	private function InstructionNumber()
	{
		$this->_number++;
		$this->_instructionNumber = "a".$this->_number;
		return $this->_instructionNumber;
	}

	private function SelectMailbox($aMailbox)
	{

		$number = $this->InstructionNumber();
		$aMailbox = strtoupper($aMailbox);

		fputs($this->_connection,"$number select $aMailbox".self::CRLF);
		$response = $this->Response($number);

		switch ($response['code'])
		{
			case self::OK:
				return true;
			
			case self::NO:
				$this->error = array('error'=>'This mailbox does not exist.');
				return false;

			case self::BAD:
				$this->error = array('error'=>'BAD! You shouldn\'t see this!');
				return false;

			default:
				$this->error = array('error'=>'Unrecognised response code');
				return false;
		}
	}

	private function NumberMessages($aMailbox)
	{
		$number = $this->InstructionNumber();
		$aMailbox = strtoupper($aMailbox);

		fputs($this->_connection,"$number status $aMailbox messages".self::CRLF);
		$response = $this->Response($number);

		switch ($response['code'])
		{
			case self::OK:
				
				break;
			
			case self::NO:
				$this->error = array('error'=>'This mailbox does not exist.');
				return false;

			case self::BAD:
				$this->error = array('error'=>'BAD! You shouldn\'t see this!');
				return false;

			default:
				$this->error = array('error'=>'Unrecognised response code');
				return false;
		}

	}
}

$stuff = new Imap();
echo $stuff->Connect('ssl://imap.gmail.com',993);

?>