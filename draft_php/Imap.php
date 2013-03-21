<?php

/*
* Name: IMAP PHP
* Author: Waqqas Sheikh
* Description: 
* - read email:header/body
* - delete email
* Comments:
* - There're a lot of repeated validation checks.
* - I'm not sure using Regex to extract the content is the most efficient way. 
* - There has got to be a better way of finding out the number of messages in the mailbox.
*/

class Imap{
	
	const RESPONSE_SIZE = 4096;
	const LOCAL_HOST = '127.0.0.1';
	const CRLF = "\r\n";

	//------------RESPONSE CODES---------//
	const OK = "OK";
	const BAD = "BAD";
	const NO = "NO";

	//----------------FLAGS--------------//
	const FLAG_ANSWERED = "\\Answered";
	const FLAG_FLAGGED = "\\Flagged";
	const FLAG_DRAFT = "\\Draft";
	const FLAG_DELETED = "\\Deleted";
	const FLAG_SEEN = "\\Seen";

	//------------- PRIVATE VARS ------------//
	private $_connection = NULL;
	private $_number = 0;
	private $_instruction_num;

	private $_connected = false;
	private $_authenticated = false;
	
	//------------- PUBLIC VARS ------------//

	public $error = array();

	function __destruct()
	{
		if($this->_connected)
			$this->logout();
	}

	function connect($imap_server, $imap_port)
	{
		if(!$this->_connected)
		{
			if($imap_server == NULL) 	$imap_server = self::LOCAL_HOST;
			if($imap_port == NULL)		$imap_port = 993;

			$this->_connection = fsockopen($imap_server,$imap_port);
		
			if(empty($this->_connection))
				$this->error = array('error' => 'Connection to server could not be established');
				
			
			else $this->_connected = true;		
		}
		
		return $this->_connected;
	}

	function login($username, $password)
	{
		if(!$this->_connected)
		{
			$this->error = array('error' => 'Not connected to the server.');
			return false;
		}

		if(!$this->_authenticated)
		{
			$instruction = $this->get_instruction_num();
			fputs($this->_connection,"$instruction LOGIN $username $password".self::CRLF);
			$response = $this->get_response($instruction);

			switch ($response['code']) {
				case self::OK:
					$this->_authenticated = true;
					break;
				
				case self::NO:
					$this->_authenticated = false;
					$this->error = array('error'=>'Invalid username or password.');
					break;

				case self::BAD:
				default: 
					$this->_authenticated = false;
					$this->error = array('error'=>$response['response']);
				break;
			}
		}

		return $this->_authenticated;
	}

	function get_header($mailbox, $message_num)
	{
		//select mailbox
		if(!$this->select_mailbox($mailbox))
			return false;

		//get number of messages
		if(!($num_msgs = $this->get_num_messages($mailbox)))
			return false;

		//checks messages in range
		if($message_num < 0 || $message_num > $num_msgs)
		{
			$this->error = array('error'=>'This message does not exist.');
			return false;
		}
		
		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction FETCH $message_num (body[header.fields (from to subject date)])".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code']) 
		{
			case self::OK:
				
				preg_match('/To: (.+?@.+)/', $response['response'],$to);
				preg_match('/From: (.+?@.+)/', $response['response'],$from);
				preg_match('/Subject: (.*)/', $response['response'],$subject);
				preg_match('/Date: (.*)/', $response['response'],$date);
				return array('number'=>$message_num,
					'from'=>$from[1],
					'to'=>$to[1],
					'subject'=>$subject[1],
					'date'=>$date[1]);

			case self::NO:
				$this->error = array('error'=>'This folder does not exist.');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}


	function get_headers($mailbox, $from, $to)
	{
		//select mailbox
		if(!$this->select_mailbox($mailbox))
			return false;

		if(!($num_msgs = $this->get_num_messages($mailbox)))
			return false;

		//checks messages in range
		if($from < 0 || $to < 0 || $from > $num_msgs || $to > $num_msgs)
		{
			$this->error = array('error'=>"List out of range. Total Messages: $num_msgs. Messages Requested: $from - $to.");
			return false;
		}

		$instruction = $this->get_instruction_num();
		$mailbox = strtoupper($mailbox);

		fputs($this->_connection,"$instruction FETCH $from:$to (body[header.fields (from to subject date)])".self::CRLF);
		$response = $this->get_response($instruction);	
	
		switch ($response['code']) {
			case self::OK:
				
				$headers = explode('*',$response['response']);
				$headers_list = array();
				foreach ($headers as $header) {
					//the first header is blank
					if($header == "")
						continue;
					//get the id, date, from, to, subject
					preg_match('/([0-9]*?) FETCH /i', $header,$message_num);
					preg_match('/To: (.+?@.+)/', $header,$to);
					preg_match('/From: (.+?@.+)/', $header,$from);
					preg_match('/Subject: (.*)/', $header,$subject);
					preg_match('/Date: (.*)/', $header,$date);
					$mail = array('number'=>$message_num[1],
					'from'=>$from[1],
					'to'=>$to[1],
					'subject'=>$subject[1],
					'date'=>$date[1]);
					array_push($headers_list, $mail);
				}

				return $headers_list;
			
			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
				break;
		}
	}

	function get_message_body($mailbox, $message_num)
	{
		//select mailbox
		if(!$this->select_mailbox($mailbox))
			return false;

		if(!($num_msgs = $this->get_num_messages($mailbox)))
			return false;

		//checks messages in range
		if($message_num<0 || $message_num>$num_msgs)
		{
			$this->error = array('error'=>'A message with this ID does not exist');
			return false;
		}
		
		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction FETCH $message_num body[text]".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code']) 
		{
			case self::OK:
				return $response['response'];

			case self::NO:
				$this->error = array('error'=>'This folder does not exist.');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	function add_flag($mailbox, $message_num,$flag)
	{
		//select mailbox
		if(!$this->select_mailbox($mailbox))
			return false;

		if(!($num_msgs = $this->get_num_messages($mailbox)))
			return false;

		//checks messages in range
		if($message_num < 0 || $message_num > $num_msgs)
		{
			$this->error = array('error'=>'This message does not exist.');
			return false;
		}
		
		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction STORE $message_num +FLAGS ($flag)".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code']) 
		{
			case self::OK:
				return true;

			case self::NO:
				$this->error = array('error'=>'This flag does not exist. Valid flags are: '.print_r(get_flags()).'.');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	function get_flags($mailbox)
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

		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction examine $mailbox".self::CRLF);
		$response = $this->get_response($instruction);

		switch($response['code'])
		{
			case self::OK:
				if(preg_match('/FLAGS \((.*?)\)/', $response['response'],$matches)!= 0)
				{
					$flags = explode(' ',$matches[1]);
					return $flags;
				}

			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}

	}

	function expunge()
	{
		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction EXPUNGE".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code']) 
		{
			case self::OK:
				return true;

			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;

		}
	}

	function get_num_messages($mailbox)
	{

		$instruction = $this->get_instruction_num();
		$mailbox = strtoupper($mailbox);

		fputs($this->_connection,"$instruction status $mailbox (messages)".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code'])
		{
			case self::OK:

				preg_match('/Messages ([0-9]+)/i', $response['response'],$matches);
				return is_numeric($matches[1])?$matches[1]:false;
			
			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
		
	}

	function logout()
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

		$instruction = $this->get_instruction_num();
		fputs($this->_connection,"$instruction LOGOUT".self::CRLF);
		$response = $this->get_response($instruction);


		switch($response['code'])
		{
			case self::OK:
				$this->_connected = !fclose($this->_connection);
				if(!$this->_connected) $this->_authenticated = false;

				return true;

			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;

		}
	}

	private function get_response($aInstructionNumber)
	{
		$end_of_response = false;

		while (!$end_of_response)
		{
			$line = fgets($this->_connection,self::RESPONSE_SIZE);
			$response .= $line.'<br/>';

			if(preg_match("/$aInstructionNumber (OK|NO|BAD)/", $response,$responseCode))
				$end_of_response = true;
		}
		
		return array('code' => $responseCode[1],
			'response'=>$response);
	} 

	private function get_instruction_num()
	{
		$this->_number++;
		$this->_instruction_num = "a".$this->_number;
		return $this->_instruction_num;
	}

	private function select_mailbox($mailbox)
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

		$instruction = $this->get_instruction_num();
		$mailbox = strtoupper($mailbox);

		fputs($this->_connection,"$instruction select $mailbox".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code'])
		{
			case self::OK:
				preg_match('/\* ([0-9]+?) EXISTS/', $response['response'],$matches);
				 return is_numeric($matches[1])?$matches[1]:false;
			
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


?>