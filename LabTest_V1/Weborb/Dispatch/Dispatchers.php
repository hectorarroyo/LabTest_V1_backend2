<?php
/*******************************************************************
 * Dispatchers.php
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

require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "Dispatch/Invoker.php");
require_once(WebOrb . "Dispatch/V3Dispatcher.php");
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

final class Dispatchers
{
    static private $s_instance = NULL;
    
    private $m_dispatchers = array();

    private function __construct() 
    {
        $this->m_dispatchers[] = new V3Dispatcher();
        $this->m_dispatchers[] = new Invoker();
    }

    static public function getInstance() 
    {
        if (is_null(self::$s_instance)) 
        {
            self::$s_instance = new Dispatchers();
        }

        return self::$s_instance;
    }

    static public function dispatch(Request &$request) 
    {
    	$instance = self::getInstance();
		
        for($i = 0; $i < count($instance->m_dispatchers); $i++)
        {
            $dispatcher = $instance->m_dispatchers[$i];

            if($dispatcher->dispatch($request))
            {
            	return true;  
            }
        }
        
        return false;
    }
}

?>
