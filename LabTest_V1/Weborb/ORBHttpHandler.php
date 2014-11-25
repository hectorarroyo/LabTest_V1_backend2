<?php
/*******************************************************************
 * ORBHttpHandler.php
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



if (!defined("WebOrb"))
{
    define("WebOrb", dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

if(ini_get("log_errors") == "")
{
	ini_set("log_errors", "1");
	ini_set("error_log", WebOrb . "orb_php_errors.txt");
}

if (!defined("WebOrbCache"))
{
    define("WebOrbCache", dirname(__FILE__) . DIRECTORY_SEPARATOR . "PollCache" . DIRECTORY_SEPARATOR);
}

require_once(WebOrb . "Dispatch" . DIRECTORY_SEPARATOR . "Dispatchers.php");
require_once(WebOrb . "Message" . DIRECTORY_SEPARATOR . "Request.php");
require_once(WebOrb . "Config" . DIRECTORY_SEPARATOR . "ORBConfig.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Network.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "ZIP" . DIRECTORY_SEPARATOR . "CreateArc.php");
require_once(WebOrb . "Writer" . DIRECTORY_SEPARATOR . "MessageWriter.php");


final class ORBHttpHandler
{

    public function __construct()
    {
        session_start();
    	if(ini_get("log_errors") == "")
		{
			ini_set("log_errors", "On");
			ini_set("error_log", WebOrb . "orb_php_errors.txt");
		}
    }

    public function processRequest()
    {

    	if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['service']) && isset($_GET['type']) )
    	{
    		$config = ORBConfig::getInstance();

    		if (!defined("WebOrbServicesPath"))
			{
				define("WebOrbServicesPath", realpath(WebOrb . $config->getServicePath() ) . DIRECTORY_SEPARATOR);
				Log::log(LoggingConstants::DEBUG, "WebORB services path is - ". WebOrbServicesPath );
			}
			$service = $_GET['service'];
			$type = $_GET['type'];
			require_once(WebOrbServicesPath . "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "ManagementService.php");
			$mS = new ManagementService();
			$arcName = "weborb.codegen.zip";
			if(file_exists(WebOrb . $arcName))
				unlink(WebOrb . $arcName);
			CreateArc::createArchive($mS->generateCode($service, null, $type, false), $arcName);
			echo("<script>document.location.href = 'Weborb/" .$arcName ."';</script>");
			unset($_GET['service']);
			unset($_GET['type']);
			exit;
    	}

        if($_SERVER["REQUEST_METHOD"] == "GET")
        {
//        	$config = ORBConfig::getInstance();
//
          print("WebORB v3.5.0");
//          //header( 'Location: ../Console/console.html' ) ;
//          return;
        }

		$timeStart = microtime(true);

        ob_start();

        $inputData = file_get_contents("php://input");

		if(isset($_FILES['Filedata']))
		{
			$config = ORBConfig::getInstance();

    		if (!defined("WebOrbServicesPath"))
			{
				define("WebOrbServicesPath", realpath(WebOrb . $config->getServicePath() ) . DIRECTORY_SEPARATOR);
				Log::log(LoggingConstants::DEBUG, "WebORB services path is - ". WebOrbServicesPath );
			}
			require_once(WebOrbServicesPath . "Weborb" . DIRECTORY_SEPARATOR . "Examples" . DIRECTORY_SEPARATOR . "Upload.php");
			upload();
			exit;
		}

//		Cache::put("inputData",$inputData);
//		$inputData = Cache::get("inputData");
        try
        {
        	$config = ORBConfig::getInstance();
			if (!defined("WebOrbServicesPath"))
			{
				define("WebOrbServicesPath", realpath(WebOrb . $config->getServicePath() ) . DIRECTORY_SEPARATOR);
				Log::log(LoggingConstants::DEBUG, "WebORB services path is - ". WebOrbServicesPath );
			}

            $request = $config->getProtocolRegistry()->buildMessage("",$inputData);

        }
        catch(Exception $e)
        {
           Log::logException(LoggingConstants::ERROR,"Internal error",$e);
           return;
        }


        if(Dispatchers::dispatch($request))
        {
            $this->serializeResponse($request);
        }

        $logMessage = sprintf("Final Execute time: %0.3f", microtime(true) - $timeStart);
		Log::log(LoggingConstants::PERFORMANCE,$logMessage);
    }

    private function serializeResponse(Request $request)
    {
        $formatter = $request->getFormatter();


        MessageWriter::writeObject($request, $formatter);

        $byteBuffer = $formatter->getBytes();

        ob_clean();

        header("Content-type: " . $formatter->getContentType());
        header("Content-length: " . strlen($byteBuffer));

        $formatter->cleanup();

        print($byteBuffer);

        ob_end_flush();
    }

}
?>
