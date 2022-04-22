<?php

class PHPMailer {
	var $host;
	var $port;
	var $from;
	var $stream;
	var $errno;
	var $errstr;
	var $timeout;
	var $start;

	function __construct($host="yourdomain.com", $port=25, $from="<no-reply@yourdomain.com>") { // Initialize class
		if ( substr($from, 0, 1) != '<' ) $from = '<'.$from; // Add < to the beginning of the e-mail address if it is missing
		if ( substr($from, strlen($from)-1, 1) != '>' ) $from = $from.'>'; // Add > to the end of the e-mail address if it is missing

		$this->host = $host;
		$this->port = $port;
		$this->from = $from;
		$this->stream = null;
		$this->errno = null;
		$this->errstr = null;
		$this->timeout = 0.02;
		$this->start = null;
	}

	private function open() {
		if ($this->stream = pfsockopen($this->host, $this->port, $this->errno, $this->errstr, 0.02)) {
			return true;
		} else {
			return false;
		}
	}

	private function close() {
		fclose($this->stream);
	}

	private function feof() {
		$this->start = microtime(true);

		return feof($this->stream);
	}

	private function read() { // Function to read response from last command send to server.
		$retVal = "";
		$timeout = ini_get('default_socket_timeout');
		/*
		while (!$this->feof() && (microtime(true) - $this->start) < $timeout) {
			$retVal .= fgets($this->stream, 4);
		}
		$this->start = null;
		*/
		while (substr($retVal, strlen($retVal)-1) != "\n") { // While end of retVal is not a line feed, do...
			$retVal .= fgets($this->stream, 4); // Read 4 bytes from the stream.
		}

		return $retVal; // Return our results.
	}

	private function write($msg) {
		fputs($this->stream, $msg);
	}

	public function send($to, $subject, $message) { // Function to actually send e-mail.
		$retVal = false; // Initialize the retVal as false. We will set it to true if we successfully send the e-mail.

		if ( substr($to, 0, 1) != '<' ) $to = '<'.$to; // Add < to the beginning of the e-mail address if it is missing
		if ( substr($to, strlen($to)-1, 1) != '>' ) $to = $to.'>'; // Add > to the end of the e-mail address if it is missing

		$cmds = [
			"HELO ".$this->host."\r\n",
			"MAIL FROM:".$this->from."\r\n",
			"RCPT TO:".$to."\r\n",
			"DATA\r\n",
			"Subject: ".$subject."\r\n\r\n",
			$message."\r\n.\r\n",
			"QUIT\r\n"
		];

		if ($this->open()) { // If we can open the connection to the mail server, attempt to send the message.
			foreach ($cmds AS $cmd) { // Iterate through our list of commands to send to the mail server in order.
				$this->write($cmd); // Send a command to the server.
				$result = $this->read(); // Get the server's response to our command.
				echo ">> $cmd\n";
				echo "<< $result\n";
				if ( stripos($result, "250 2.6.0") !== false ) $retVal = true; // If the server responds with a '250 2.6.0' message (no matter what the wording of the response) that is a mail queued for delivery message and that means success so set our return value to true.
				if ( stripos($result, "Queued mail for delivery") !== false) $retVal = true; // If the server responds with a message containing 'Queued mail for delivery' that is a success, so set the return value to true.
				if ( stripos($result, "queued as") !== false ) $retVal = true; // If the server responds with a message containing 'queued as' that is a success, so set the return value to true.
			}
			$this->close(); // Close the connection to the mail server.
		}
		return $retVal; // Return our results.
	}
}

?>
