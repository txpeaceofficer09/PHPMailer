<?php

class PHPMailer {
	var $host;
	var $port;
	var $from;
	
	function __construct($host="yourdomain.com", $port=25, $from="<no-reply@yourdomain.com>") {
		if ( substr($from, 0, 1) != '<' ) $from = '<'.$from;
		if ( substr($from, strlen($from)-1, 1) != '>' ) $from = $from.'>';
		
		$this->host = $host;
		$this->port = $port;
		$this->from = $from;
	}
	
	public function send($to, $subject, $message) {
		$retVal = false;

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
		
		if ($fp=pfsockopen($this->host, $this->port, $errno, $errstr, 0.02)) {
			foreach ($cmds AS $cmd) {
				fputs($fp, $cmd);
				$result = fgets($fp, 2048);
				if ( strstr($result, "Queued mail for delivery") !== false) $retVal = true;
			}
			fclose($fp);
		}
		return $retVal;
	}
}

?>
