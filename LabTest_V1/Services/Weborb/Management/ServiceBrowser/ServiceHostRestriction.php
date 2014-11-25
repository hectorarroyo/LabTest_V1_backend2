<?php
/*******************************************************************
 * ServiceHostRestriction.php
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

require_once("ServiceRestriction.php");
require_once(WebOrb. "Security/HostNameRestriction.php");

class ServiceHostRestriction extends ServiceRestriction
{
	var $HostName;        
    static private $s_instance = NULL;
        
	static public function getInstance()
	{
		if (is_null(self::$s_instance))
	    {
	    	self::$s_instance = new ServiceHostRestriction();
	    }
	    
	    return self::$s_instance;
	}
    
    public function ServiceHostRestriction($hostNameRestriction)
    {
    	if($hostNameRestriction instanceof HostNameRestriction)
        	$this->HostName = $hostNameRestriction->getHostName();    
        else
            $this->HostName = $hostNameRestriction; 
    }
        
    function applyParameters($parameters){
        $this->HostName = $parameters["HostName"];
    }
    	
    public function Convert()
    {
    	return new HostNameRestriction($this->HostName);
    }
}
?>