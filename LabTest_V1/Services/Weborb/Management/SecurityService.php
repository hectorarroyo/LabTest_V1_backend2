<?php
/*******************************************************************
 * SecurityService.php
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

require_once("ServiceBrowser/ServiceRestrictionType.php");
require_once("ServiceBrowser/SecurityConfigurator.php");

class SecurityService
{
	public function getRestrictionTypes()
	{
		$serviceRestrictionTypeList = array();
		$serviceRestrictionTypeList[] = new ServiceRestrictionType(ServiceRestrictionType::ROLE, "Role");
		$serviceRestrictionTypeList[] = new ServiceRestrictionType(ServiceRestrictionType::HOST, "Host");
        $serviceRestrictionTypeList[] = new ServiceRestrictionType(ServiceRestrictionType::IPRange, "IP Range");
        $serviceRestrictionTypeList[] = new ServiceRestrictionType(ServiceRestrictionType::IP, "Single IP");

        return $serviceRestrictionTypeList;
	}

	public function applyRestriction($resource, $constraintName, $grant, $restriction)
    {
    	SecurityConfigurator::getInstance()->applyRestriction($resource, $constraintName, $grant, $restriction);

    	return $grant;
    }

    public function removeRestriction($constraintName, $restriction)
    {
        SecurityConfigurator::getInstance()->removeRestriction($constraintName, $restriction);
    }

    public function removeConstraint($resource, $constraintName)
    {
        SecurityConfigurator::getInstance()->removeConstraint($resource, $constraintName);
    }

    public function getRoles()
    {
        return ORBConfig::getInstance()->getSecurity()->getRolesProvider()->getRoles();
    }
}
?>
