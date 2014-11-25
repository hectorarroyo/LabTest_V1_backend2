<?php
class ServerConfiguration 
{
	private static /*String*/ $DEFAULT_URL = "http://localhost:8080";	  
	private static /*int*/ $DEFAULT_TIMEOUT = 30;	  
	private static /*int*/ $DEFAULT_POLLING_TIMEOUT = 5000;
	
	public /*String*/ $serverAddress;
	public /*int*/ $reconnectionTimeout;
	public /*int*/ $pollingTimeout;
	public function __construct()
	{
		$this->serverAddress = self::$DEFAULT_URL;
		$this->reconnectionTimeout = self::$DEFAULT_TIMEOUT;
		$this->pollingTimeout = self::$DEFAULT_POLLING_TIMEOUT;
	}
}
?>