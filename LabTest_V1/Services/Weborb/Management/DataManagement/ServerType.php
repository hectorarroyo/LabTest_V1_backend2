<?php
/*******************************************************************
 * ServerType.php
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
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/DatabaseInfoType.php");
class ServerType
{
    public $Name;
    public $Id;

    public function __construct($databaseType = null)
    {
    	if ($databaseType)
    	{
	    	$this->Id = (int) $databaseType;
	
	        switch ($databaseType)
	        { 
	            case DatabaseInfoType::MYSQL:
	                $this->Name = "MySQL";
	                break;
	        	case DatabaseInfoType::MSSQL:
	                $this->Name = "Microsoft SQL Server";
	                break;

	            default:
					$this->Name = "Unknown Database";
	                break;
	        }    		 
    	}
    }
}
?>