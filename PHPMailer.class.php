<?php

class PHPMailer {
	var $host;
	var $port;
	var $from;
	
	function __construct($host="yourdomain.com", $port=25, $from="<no-reply@yourdomain.com>") { // Initialize class
		if ( substr($from, 0, 1) != '<' ) $from = '<'.$from;
		if ( substr($from, strlen($from)-1, 1) != '>' ) $from = $from.'>';
		
		$this->host = $host;
		$this->port = $port;
		$this->from = $from;
	}
	
	private function readStream($socket) { // Function to read response from last command send to server.
		$retVal = "";
		$i = 0;

		while (substr($retVal, strlen($retVal)-1, 1) != "\n" && $i < 512) { // Keep reading from socket until the last of the return value is a line feed or we have run the loop 512 times.
			$retVal .= fgets($socket, 4); // Get 4 characters from socket.
			$i++; // Add 1 to i.
		}

		return $retVal; // Return our results.
	}
	
	public function send($to, $subject, $message) { // Function to actually send e-mail.
		$retVal = false; // Initialize the retVal as false. We will set it to true if we successfully send the e-mail.

		if ( substr($to, 0, 1) != '<' ) $to = '<'.$to;
		if ( substr($to, strlen($to)-1, 1) != '>' ) $to = $to.'>';
		
		$cmds = [
			"HELO ".$this->host."\r\n",
			"MAIL FROM:".$this->from."\r\n",
			"RCPT TO:".$to."\r\n",
			"DATA\r\n",
			"Subject: ".$subject."\r\n\r\n",
			$message."\r\n.\r\n",
			"QUIT\r\n"
		];
		
		if ($fp=pfsockopen($this->host, $this->port, $errno, $errstr, 0.02)) { // If we can open the connection to the mail server, attempt to send the message.
			foreach ($cmds AS $cmd) { // Iterate through our list of commands to send to the mail server in order.
				fputs($fp, $cmd); // Send a command to the server.
				$result = fgets($fp, 2048); // Get the server's response to our command.
				if ( strstr($result, "250 2.6.0") !== false ) $retVal = true; // If the server responds with a '250 2.6.0' message (no matter what the wording of the response) that is a mail queued for delivery message and that means success so set our return value to true.
				if ( strstr($result, "Queued mail for delivery") !== false) $retVal = true; // If the server responds with a message containing 'Queued mail for delivery' that is a success, so set the return value to true.
				if ( strstr($result, "queued as") !== false ) $retVal = true; // If the server responds with a message containing 'queued as' that is a success, so set the return value to true.
			}
			fclose($fp); // Close the connection to the mail server.
		}
		return $retVal; // Return our results.
	}
}

?>
