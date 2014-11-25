<?php
/*******************************************************************
 * Invoker.php
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

require_once(WebOrb . "Dispatch/IDispatch.php");
require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "Handlers/Handlers.php");
require_once(WebOrb . "Handlers/IInvocationHandler.php");
require_once(WebOrb . "Message/Body.php");
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util/Value.php");
require_once(WebOrb . "Registry/ServiceRegistry.php");
require_once(WebOrb . "Exceptions/ServiceException.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

class Invoker
    implements IDispatch
{

    public function __construct()
    {
    }

    public function dispatch(Request &$request)
    {
        $config = ORBConfig::getInstance();

        for($i = 0; $i < $request->getBodyCount(); $i++)
        {
            $request->setCurrentBody($i);

            $requestURI = $request->getRequestURI();

            Log::log(LoggingConstants::INFO,
                "requestURI = $requestURI");

            $serviceId = substr($requestURI, 0 , strrpos($requestURI,"."));
            $methodName = substr($requestURI, strlen($serviceId) + 1);

            $arg = $request->getRequestBodyData();

            if(!is_array($arg))
            {
              $arg = array($request->getRequestBodyData());
            }

            Log::log(LoggingConstants::DEBUG,
                "requestURI = $requestURI, "
                . "serviceId = $serviceId, methodName = $methodName, bodyDataType:"
                . gettype($request->getRequestBodyData()) . " className:"
                . get_class($request->getRequestBodyData()));


			try
			{
            	$value = self::handleInvoke($request, $serviceId, $methodName, $arg);
				
            	$request->setResponseBodyPart($value);
            	$request->setResponseURI("/onResult");
            }
            catch( Exception $e )
            {
            	$request->setResponseBodyPart($e);
            	$request->setResponseURI("/onStatus");
            }
        }

        return true;
    }

    public static function handleInvoke(Request $request, $targetObject, $function, &$arg)
    {
        $config = ORBConfig::getInstance();
        $handlers = $config->getHandlers();
        $resolvedName = ServiceRegistry::getMapping($targetObject);

		if( !$config->getSecurity()->canAccess( $resolvedName ) )
		{
			throw new ServiceException( "WebORB security has rejected access to class " . $targetObject . ". see server log or contact system administrator", 401 );
		}

		if( !$config->getSecurity()->canAccess( $resolvedName . "#" . $function ) )
		{
			throw new ServiceException( "WebORB security has rejected access to method " . $targetObject . "." . $function . ". see server log or contact system administrator", 401 );
		}

		$timeStart = microtime(true);

        $value = $handlers->invoke($resolvedName, $function ,$arg);
		$logMessage =  sprintf("Service \"$resolvedName::$function\" execute time: %0.3f", microtime(true) - $timeStart);

        Log::log(LoggingConstants::PERFORMANCE,$logMessage);

        return $value->getObject();
    }
}

?>
