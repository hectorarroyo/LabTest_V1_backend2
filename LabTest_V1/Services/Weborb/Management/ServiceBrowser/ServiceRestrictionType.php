<?php
/*******************************************************************
 * ServiceRestrictionType.php
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

class ServiceRestrictionType
{

	const ROLE = 0;
    const HOST = 1;
    const IPRange = 2;
    const IP = 3;
    
    var $Id;
    var $Name;
    
	public function ServiceRestrictionType($type, $name) 
    { 
    	$this->Id = $type;
        $this->Name = $name;
    }
        	
	public function IsRole() 
	{ 
		return ServiceRestrictionType::ROLE == $this->Id;
	}
    public function IsHost() 
    {    	
    	return ServiceRestrictionType::HOST == $this->Id;
    }
    public function IsIPRange() 
    { 
    	return ServiceRestrictionType::IPRange == $this->Id;
    }
    public function IsIP() 
    { 
    	return ServiceRestrictionType::IP == $this->Id;
    }
}

?>

