<?php
/*******************************************************************
 * Log.php
 * Copyright (C) 2006-2007 Midnight Coders, LLC
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is WebORB Presentation Server (R) for PHP.
 * 
 * The Initial Developer of the Original Code is Midnight Coders, LLC.
 * All Rights Reserved.
 ********************************************************************/
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

class Log
{
	static private $s_logger;
	static private $s_filter;

 	private function __construct(){}
	
	public static function setLogger(ILogger $logger)
	{
		self::$s_logger = $logger;
	}
	
	public static function startLogging($channel)
	{
		if((self::$s_filter & $channel) == 0)
			self::$s_filter |= $channel;
	}

	public static function stopLogging($channel)
	{
		if((self::$s_filter & $channel) != 0)
			self::$s_filter &= ~$channel;
	}
	
    public static function isChannelAllowed($channel)
    {
      return (self::$s_filter & $channel) != 0;
    }

    public static function logException($channel, $message, Exception $exception)
    {
       if(!self::isChannelAllowed($channel))
            return;

        $line = date("c");
        $line .= " [" . LoggingConstants::getLogChannelName($channel) . "] ";
        $line .= self::getSource();
        $line .= $message . "\r\n";
		$line .= $exception->__toString() . "\r\n";

        self::$s_logger->log($line);
    }

    public static function log($channel, $message)
    {
    	if(!self::isChannelAllowed($channel) || !self::$s_logger)
            return;

        $line = date("c");
        $line .= " [" . LoggingConstants::getLogChannelName($channel) . "] ";
        $line .= self::getSource();
        $line .= $message . "\r\n";

        $source = debug_backtrace();

        self::$s_logger->log($line);
    }

    public static function getSource()
    {
        $bt = debug_backtrace();

        if (isset($bt[2]))
        {
            $class = $bt[2]['class'];
            $function = $bt[2]['function'];

            $file = $bt[1]['file'];
            $line = $bt[1]['line'];

            return "$class::$function:";
        }

        return "";
    }
}

?>
