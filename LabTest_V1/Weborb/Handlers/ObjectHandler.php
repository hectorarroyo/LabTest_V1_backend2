<?php
/*******************************************************************
 * ObjectHandler.php
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

require_once(WebOrb . 'Util/TypeLoader.php');
require_once(WebOrb . 'Util/MethodLookup.php');
require_once(WebOrb . 'Util/Invocation.php');
require_once(WebOrb . 'Util/Logging/Log.php');
require_once(WebOrb . "Util/Logging/LoggingConstants.php");
require_once(WebOrb . 'Exceptions/InvocationException.php');
require_once(WebOrb . 'Handlers/IInvocationHandler.php');
require_once(WebOrb . "Util/Cache/Cache.php");
require_once WebOrb . 'Registry/MonitoredClass.php';

class ObjectHandler
    implements IInvocationHandler
{

    public function __construct()
    {
    }

    public function invoke($serviceName, $methodName, $arguments)
    {
    	Log::log(LoggingConstants::INFO,
            "ServiceName = $serviceName, "
            . "methodName = $methodName, count(arguments) = " . count($arguments));
		$class = TypeLoader::loadType($serviceName);
		$object = $this->resolveObject($class);

        $method = MethodLookup::findMethod($class, $methodName);
        $startInvoke = microtime(true);
        $result = Invocation::invoke($object, $method, $arguments);
        $invokeTime = microtime(true)-$startInvoke;
        $this->addMonitoredClass($class, $serviceName, $methodName, $invokeTime, $arguments, $result);
        
        return new Value($result);
    }
    
    private function addMonitoredClass($class, $serviceName, $methodName, $invokeTime, $args, $result)
    {
    	$fullName = $serviceName . '.' . $methodName;
    	$monitoredClassRegistry = ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry();
    	if($monitoredClassRegistry->isSelected($fullName) != ServiceNode::NOT_SELECTED)
    	{
    		$monitoredClass = Cache::get($fullName);
    		if($monitoredClass != null)
    		{
    			$monitoredClass->addInvoke($invokeTime, $args, $result);
    			Cache::put($fullName, $monitoredClass);
    		}
    		else 
    		{
    			$monitoredClass = new MonitoredClass($class, $fullName, $methodName);
    			$monitoredClass->addInvoke($invokeTime, $args, $result);
    			Cache::put($fullName, $monitoredClass);
    		}
    	}
    }

    public function getName()
    {
        return 'ObjectHandler';
    }

    private function resolveObject(ReflectionClass $class)
    {
        try
        {
            return $class->newInstance();
        }
        catch (ReflectionException $re)
        {
            throw new InvocationException($re->getMessage());
        }
    }

}

?>
