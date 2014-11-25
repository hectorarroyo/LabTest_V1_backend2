<?php
/*******************************************************************
 * ServiceRestriction.php
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

require_once(WebOrb. "Security/RoleNameRestriction.php");
require_once(WebOrb. "Security/HostNameRestriction.php");
require_once(WebOrb. "Security/IPRangeRestriction.php");
require_once(WebOrb. "Security/SingleIPRestriction.php");
require_once("ServiceRoleRestriction.php");
require_once("ServiceHostRestriction.php");
require_once("ServiceIPRangeRestriction.php");
require_once("ServiceIPRestriction.php");

class ServiceRestriction
{
    var $Constraint; 
    
    public static function CreateInstance($restriction)
    {
        if ($restriction instanceof RoleNameRestriction)
        {    	
			$serviceRoleRestriction = new ServiceRoleRestriction($restriction); 
			$serviceRoleRestriction->RoleName=$restriction->getRoleName();
			
            return $serviceRoleRestriction;
        }
        
        else if ($restriction instanceof HostNameRestriction)
        {       	
            return new ServiceHostRestriction($restriction);
        }
        
        else if ($restriction instanceof IPRangeRestriction)
        {  
        	$serviceIPRangeRestriction = new ServiceIPRangeRestriction($restriction); 
			$serviceIPRangeRestriction->SubnetAddress = $restriction->getSubnetAddress();
			$serviceIPRangeRestriction->SubnetMask = $restriction->getSubnetMask();
		
            return $serviceIPRangeRestriction;
        }
        
        else if ($restriction instanceof SingleIPRestriction)
        {
        	$serviceIPRestriction = new ServiceIPRestriction($restriction); 
			$serviceIPRestriction->IPAddress = $restriction->ipAddress();	
			
            return $serviceIPRestriction;
        }
    }
    
	public static function CreateInstance2($restrictionType)
    {
        if ($restrictionType->IsRole())
        {
        	$restriction = ServiceRoleRestriction::getInstance();
        	       	
            return $restriction;
        }
        else if ($restrictionType->IsHost())
        {
        	$restriction = ServiceHostRestriction::getInstance(); 
        	      	
            return $restriction;
        }
        else if ($restrictionType->IsIPRange())
        {
        	$restriction = ServiceIPRangeRestriction::getInstance();
        	       	
            return $restriction;
        }
        else if ($restrictionType->IsIP())
        {
        	$restriction = ServiceIPRestriction::getInstance();
        	       	
            return $restriction;
        }
    }
    
}

?>
