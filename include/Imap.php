<?php

/*@
* 
*
*@class: IMAP
*@author: Waqqas Sheikh
* I know I should have used enums for flags and fields.
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

	const FIELD_NUMBER = 'number';
	const FIELD_TO = 'to';
	const FIELD_FROM = 'from';
	const FIELD_SUBJECT = 'subject';
	const FIELD_DATE = 'date';
	const FIELD_FLAG = 'flags';

	const MAILBOX_INBOX = 'INBOX';

	//------------- PRIVATE VARS ------------//
	private $_connection = NULL;
	private $_number = 0;
	private $_instruction_num;

	private $_connected = false;
	private $_authenticated = false;
	
	//------------- PUBLIC VARS ------------//

	public $error = array();

	/**
	*Connects to Imap server.
	*@param string $imap_server ssl ip address of imap server e.g. imap.gmail.com
	*@param integer $imap_port ssl port of imap server
	*@throws exception 
	*/
	function __construct($imap_server, $imap_port)
	{
		$this->connect($imap_server,$imap_port);
	}

	/**
	*
	*Destructor: quits imap session and closes connection
	*
	*/
	function __destruct()
	{
		if($this->_connected)
			$this->logout();
	}

	/**
	*connects to imap server
	*@param string $imap_server ssl ip address of imap server. 
	*@param integer $imap_port ssl port of imap server
	*@throws exceptions
	*@return boolean
	*/
	private function connect($imap_server, $imap_port)
	{
		if($this->_connected == false)
		{
			if($imap_server == NULL) 	$imap_server = self::LOCAL_HOST;
			if($imap_port == NULL)		$imap_port = 993;

			$this->_connection = fsockopen($imap_server,$imap_port);
		
			//check that connection was established.
			if(!is_resource($this->_connection))
			{
				throw new Exception('Connection to server could not be established');
			}
			else $this->_connected = true;		

		}
		return $this->_connected;
	}

	/**
	*authenticates user
	*@param string $username full mail username (unencrypted) e.g. bob@gmail.com
	*@param integer $password unencrypted mal password
	*@return boolean
	*/
	function login($username, $password)
	{
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
					$this->error = array('error'=>'Authentication Failed.');
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

	/**
	*returns the header (from, to, subject, date) of a message
	*
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@param integer $message_num number of message to be deleted (not unique id)
	*@return array($imap::FIELD_FROM,$imap::FIELD_TO,$imap::FIELD_SUBJECT,$imap::FIELD_DATE) or false.
	*/
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
				//extract header data
				preg_match('/To: (.+?@.+)/', $response['response'],$to);
				preg_match('/From: (.+?@.+)/', $response['response'],$from);
				preg_match('/Subject: (.*)/', $response['response'],$subject);
				preg_match('/Date: (.*)/', $response['response'],$date);
				return array(self::FIELD_NUMBER =>$message_num,
					self::FIELD_FROM=>($this->clean($from[1])),
					self::FIELD_TO=>($this->clean($to[1])),
					self::FIELD_SUBJECT=>$subject[1],
					self::FIELD_DATE=>$date[1]);

			case self::NO:
				$this->error = array('error'=>'Header could not be retrieved');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	/**
	*Returns headers (from, to, subject, date) of all mails between $from and $to
	*
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@param integer $from first mail number (use "*" to get all mails)
	*@param integer $to last mail number
	*@return array(array($imap::FIELD_FROM,$imap::FIELD_TO,$imap::FIELD_SUBJECT,$imap::FIELD_DATE)) or false.
	*/
	function get_headers($mailbox, $from, $to)
	{
		//select mailbox
		if(!$this->select_mailbox($mailbox))
			return false;

		if(!($num_msgs = $this->get_num_messages($mailbox)))
			return false;

		if($from == "*")
			$from = $num_msgs;

		//checks messages in range
		if($from < 0 || $to < 0 || $from > $num_msgs || $to > $num_msgs)
		{
			$this->error = array('error'=>"List out of range. Total Messages: $num_msgs. Messages Requested: $from - $to.");
			return false;
		}

		//get instruction number
		$instruction = $this->get_instruction_num();
		$mailbox = strtoupper($mailbox);

		//send command to server
		fputs($this->_connection,"$instruction FETCH $from:$to (body[header.fields (from to subject date)] flags)".self::CRLF);
		$response = $this->get_response($instruction);	

		switch ($response['code']) {
			case self::OK:
				$headers = explode('*',$response['response']);

				//store all headers in list.
				$headers_list = array();
				foreach ($headers as $header) {

					//ignore blank headers
					if($header == "")
						continue;

					//get the id, date, from, to, subject
					preg_match('/([0-9]*?) FETCH /i', $header,$message_num);
					preg_match('/FLAGS \((.*?)\)/i',$header,$flags);
					preg_match('/To: (.+?@.+)/', $header,$to);
					preg_match('/From: (.+?@.+)/', $header,$from);
					preg_match('/Subject: (.*)/', $header,$subject);
					preg_match('/Date: (.*)/', $header,$date);
				
					//add details to header
					$mail = array(self::FIELD_NUMBER=>$message_num[1],
					self::FIELD_FLAG=> $flags[1],
					self::FIELD_FROM=>($this->clean($from[1])),
					self::FIELD_TO=>($this->clean($to[1])),
					self::FIELD_SUBJECT=>$subject[1],
					self::FIELD_DATE=>$date[1]);

					//push header to header list.
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

	/**
	*Returns text of mail with given message number
	*
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@param integer $message_num message number
	*@return string or false
	*/
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
				return $this->process_message($response['response']);

			case self::NO:
				$this->error = array('error'=>'Message could not be retrieved');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	/**
	*Adds flag($imap::FLAG_DELETED,$imap::FLAG_SEEN etc) to a message
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@param integer $message_num message number
	*@param string $flag flag
	*@return boolean
	*/
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
				$this->error = array('error'=>'This flag does not exist.');
				return false;

			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	/**
	*deletes mail
	*
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@param integer $message_num mail number
	*@return boolean
	*
	*/
	function delete_mail($mailbox,$message_num)
	{
		$this->add_flag($mailbox,$message_num,self::FLAG_DELETED);
	}

	/*
	*Returns available flags
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@return array of flags or false
	*
	*
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
				$this->error = array('error'=>'FATAL: Flags could not be determined from respose.');
				return false;

			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}

	}*/

	/**
	*Completely deletes mails
	*@return boolean
	*/
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

	/**
	*retuns number of messages in mailbox
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@return integer/boolean
	*
	*/
	function get_num_messages($mailbox)
	{

		$instruction = $this->get_instruction_num();
		$mailbox = strtoupper($mailbox);

		fputs($this->_connection,"$instruction status $mailbox (messages)".self::CRLF);
		$response = $this->get_response($instruction);

		switch ($response['code'])
		{
			case self::OK:

				if(preg_match('/Messages ([0-9]+)/i', $response['response'],$matches)!= 0)
					return $matches[1];
				$this->error = array('error'=>'FATAL: Number of messages could not be determined!');
				return false;
			
			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
		
	}


	/**
	*logs out user and closes imap connection
	*@return boolean
	*/
	function logout()
	{
		if(!$this->_connected)
			return true;

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

	/**
	*returns last error message
	*@return string
	*
	*/
	function error()
	{
		return $this->error['error'];
	}

	/**
	*returns response for request sent to imap socket
	*@param $aInstructionNumber instruction number sent with request
	*@return array('code','response')
	*@throws Exception if connection has expired.
	*/
	private function get_response($aInstructionNumber)
	{

		$end_of_response = false;

		if(!is_resource($this->_connection))
			throw new Exception('IMAP socket connection terminated');

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

	/**
	*retuns new instruction number
	*@return string
	*
	*/
	private function get_instruction_num()
	{
		$this->_number++;
		$this->_instruction_num = "a".$this->_number;
		return $this->_instruction_num;
	}

	/**
	*selects mailbox
	*@param string $mailbox name of mailbox, use $imap::MAILBOX_INBOX
	*@return boolean
	*/
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
				return true;

			case self::NO:
			case self::BAD:
			default:
				$this->error = array('error'=>$response['response']);
				return false;
		}
	}

	private function process_message($message)
	{
		$processed_message  = '';
		//get plain text part;
		$start = strpos($message, "Content-Type: text/plain");
		$end = strpos($message, "Content-Type: text/html");
		$msg = substr($message, $start,$end);

		if($msg == "")
			$msg = $message;

		$lines = explode('<br/>', $msg);
		foreach ($lines as $line){
			if($line === ""
				|| strpos($line, "Content-Type") !== false
				|| strpos($line, "Content-Transfer-Encoding") !== false
				|| strpos($line, "FETCH") !== false
				|| strpos($line, "OK Success" )!== false)
				continue;

			$processed_message .= $line.'<br/>';
		}

		$from = array('=0D','=1D','=2D','=3D','=4D','=5D','=6D','==');
		$to = array('',' ','  ','   ','    ','     ','      ','-');
		return str_replace($from, $to, $processed_message);
	}


	/**
	*decodes html tags (I tried using html_entity_decode but that didnt work for some reason)
	*@param string $raw encoded html
	*@return string decoded html
	*
	*/
	private function clean($raw)
	{
		$from = array('<','>','"',"'");
		$to = array('&lt;','&gt','',"");
		$clean =  str_replace($from, $to, $raw);
		return $clean;
	}


}
?>