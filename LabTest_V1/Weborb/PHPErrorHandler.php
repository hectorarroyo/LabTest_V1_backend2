<?php
/*******************************************************************
 * PHPErrorHandler.php
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
require_once(WebOrb . "Util/Logging/Log.php");

//error_reporting(0);
//set_error_handler('phpErrorHandler');

function phpErrorHandler($severity, $message, $filename, $lineno)
{
	switch($severity)
	{
		case E_ERROR:
		case E_WARNING:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			Log::logException(LoggingConstants::ERROR,"Unexpected error", ErrorException($message, 0, $severity, $filename, $lineno));
			break;
		case E_RECOVERABLE_ERROR:
		case E_USER_ERROR:
			$exception = new ErrorException($message, 0, $severity, $filename, $lineno);
			Log::logException(LoggingConstants::ERROR,"Unexpected error", $exception);
			throw $exception;
			break;
		case E_USER_NOTICE:
		case E_NOTICE:
			Log::log(LoggingConstants::INFO, "PHP notice: $message, file:$filename, line:$lineno");
			break;
		default:
			Log::log(LoggingConstants::DEBUG, "PHP notice: $message, file:$filename, line:$lineno");
	}

}


?>