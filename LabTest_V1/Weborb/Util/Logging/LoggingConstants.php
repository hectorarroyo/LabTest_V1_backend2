<?php
/*******************************************************************
 * LoggingConstants.php
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
class LoggingConstants
{   
    const INFO          	= 0x0002;
    const DEBUG     		= 0x0004;  
    const ERROR   	    	= 0x0008;      
    const SERIALIZATION 	= 0x0010;   
    const EXCEPTION     	= 0x0020;
    const INSTRUMENTATION 	= 0x0040;
    const SECURITY			= 0x0080;
    const PERFORMANCE		= 0x0100;
    const MYDEBUG     		= 0x0200;
    const REF     		    = 0x0400;
    
    private static $s_names = null;
    private static $s_channels = null;
        
    public static function getLogChannelName($logType)
    {
      if(self::$s_names == null)
      {
         self::$s_names = array();
         self::$s_names[LoggingConstants::INFO] 			= "WEBORB INFO";
         self::$s_names[LoggingConstants::DEBUG] 			= "WEBORB DEBUG";
         self::$s_names[LoggingConstants::ERROR] 			= "WEBORB ERROR";
         self::$s_names[LoggingConstants::SERIALIZATION] 	= "WEBORB SERIALIZATION";
         self::$s_names[LoggingConstants::EXCEPTION] 		= "WEBORB EXCEPTION";
		 self::$s_names[LoggingConstants::INSTRUMENTATION] 	= "WEBORB INSTRUMENTATION";
         self::$s_names[LoggingConstants::SECURITY] 		= "WEBORB SECURITY";   
         self::$s_names[LoggingConstants::PERFORMANCE] 		= "WEBORB PERFORMANCE"; 
         self::$s_names[LoggingConstants::MYDEBUG] 			= "WEBORB MYDEBUG";           
         self::$s_names[LoggingConstants::REF]	 			= "REF COUNT";           
      }
      
      return self::$s_names[$logType];
    }
    
    
    public static function getChannelByName($name)
    {
      if(self::$s_channels == null)
      {
         self::$s_channels = array();
         self::$s_channels["WEBORB INFO"] 				= 0x0002;
         self::$s_channels["WEBORB DEBUG"] 				= 0x0004;
         self::$s_channels["WEBORB ERROR"] 				= 0x0008;
         self::$s_channels["WEBORB SERIALIZATION"] 		= 0x0010;
         self::$s_channels["WEBORB EXCEPTION"] 			= 0x0020;  
         self::$s_channels["WEBORB INSTRUMENTATION"] 	= 0x0040;      
         self::$s_channels["WEBORB SECURITY"] 			= 0x0080;   
         self::$s_channels["WEBORB PERFORMANCE"] 		= 0x0100;
         self::$s_channels["WEBORB MYDEBUG"] 			= 0x0200;            
         self::$s_channels["REF COUNT"] 	 		    = 0x0400;            
      }
      
      return self::$s_channels[$name];	
    }
}

?>
