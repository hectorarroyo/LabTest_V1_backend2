<?php
/*******************************************************************
 * Handlers.php
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
require_once(WebOrb . "Util/Logging/Log.php");

class Handlers
{

    private $m_invHandlers = array();
    private $m_inspHandlers = array();

    public function __construct()
    {
    }

    public function addInvocationHandler($handler)
    {
        $this->m_invHandlers[] = $handler;
    }

    public function addInspectionHandler($handler)
    {
        $this->m_inspHandlers[] = $handler;
    }

    public function invoke($className, $methodName, $arguments)
    {
        Log::log(LoggingConstants::DEBUG, "Called");

        for ($index = 0; $index < count($this->m_invHandlers); $index ++)
        {
            $value = $this->m_invHandlers[$index]->invoke($className, $methodName, $arguments);

            if($value != NULL)
            {
                return $value;
            }
        }

        return NULL;
    }

    public function inspect($className, $methodName, $arguments)
    {
        Log::log(LoggingConstants::DEBUG, "Called");

        for ($index = 0; $index < count($this->m_inspHandlers); $index ++)
        {
            $this->m_inspHandlers[$index]->inspect($className, $methodName, $arguments);
        }
    }

}

?>
