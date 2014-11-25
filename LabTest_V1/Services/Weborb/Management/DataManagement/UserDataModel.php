<?php
/*******************************************************************
 * UserDataModel.php
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
require_once("UserClassInfo.php");
class UserDataModel
{
	private /*DatabaseConnectionInfo*/$databaseConnectionInfo;
	
    public $Name;
    public $Tables;
    public $ServerNamespace;
    public $ClientNamespace;
    public $ServerLanguage;
    public $UserClasses;
    public $Build;
    public $IsGenerateTestDrive;
    public $IsGenerateUnitTests;
    public $IsGenerated;
    public $LOC;

    public function __construct()
    {
        $this->Name = "";
        $this->Tables = array();
        $this->UserClasses = array();
        $this->serverConnection = null;
    }
	
    public function setServerConnection(DatabaseConnectionInfo $_databaseConnectionInfo)
    {
    	$this->databaseConnectionInfo = $_databaseConnectionInfo;
    }
    
    public /*DatabaseConnectionInfo*/function getServerConnection()
    {
    	return $this->databaseConnectionInfo;
    }
    
    public function GetClassInfo($databaseConnectionId, $tableName)
    {
    	foreach ($this->UserClasses as $userClass)
    	{
    		if ($userClass->TableName == $tableName && $userClass->DatabaseConnectionInfoId == $databaseConnectionId)
    			return $userClass;
    	}
    	
    	return null;
    }

    public function UpdateClassInfo(UserClassInfo $classInfo)
    {
        $currentClassInfo = $this->GetClassInfo($classInfo->DatabaseConnectionInfoId, $classInfo->TableName);

        if ($currentClassInfo != null)
            $this->removeItemFromArray($currentClassInfo, $this->UserClasses);

        $this->UserClasses[] = $classInfo;
    }

    private function removeItemFromArray($val,&$arr)
	{
		$i = array_search($val,$arr);
		if ($i === false) return false;
		$arr=array_merge(array_slice($arr, 0,$i), array_slice($arr, $i+1));

		return true;
	}
}
?>