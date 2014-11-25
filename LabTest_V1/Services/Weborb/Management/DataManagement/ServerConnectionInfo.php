<?php
/*******************************************************************
 * ServerConnectionInfo.php
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
require_once("ServerType.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/DatabaseInfo.php");

class ServerConnectionInfo
{

    public $HostName;
    public $Port;
    public $UserName;
    public $Password;
    public /*ServerType*/ $Type;

    public function __construct()
    {
    	$this->Type = new ServerType();
    }
    
    public function GetInspector()
    {
       	$databaseInfo = new DatabaseInfo(
								        	$this->Type->Id,
								        	$this->HostName,
								        	$this->Port == null ? "" : $this->Port,
									        $this->UserName,
								            $this->Password); 

		return $databaseInfo->GetInspector();
    }

}
?>