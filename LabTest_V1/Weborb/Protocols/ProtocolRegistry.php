<?php
/*******************************************************************
 * ProtocolRegistry.php
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



require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Exceptions/UnknownRequestFormatException.php");
require_once(WebOrb . "Protocols/IMessageFactory.php");

class ProtocolRegistry
{

    private $m_factories = array();

    public function addMessageFactory(IMessageFactory $factory) 
    {
        $this->m_factories[] = $factory;
    }

    public function buildMessage($contentType, $stream)
    {
        $request = null;
        
        for($i = 0; $i < count($this->m_factories); $i++)
        {
            if($this->m_factories[$i]->canParse($contentType))
            {
                $request =  $this->m_factories[$i]->parse($stream);
                break;
            }
        }

        if($request == null)
        {
            throw new UnknownRequestFormatException("cannot parse request. 
                possible reasons: malformed request or protocol formatter is not registered ");
        }
        
        return $request;
    }

}

?>
