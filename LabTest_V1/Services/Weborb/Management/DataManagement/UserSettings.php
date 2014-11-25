<?php
/*******************************************************************
 * UserSettings.php
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
require_once("DatabaseConnectionInfo.php");
require_once("UserDataModel.php");
require_once("UserTableInfo.php");
require_once("UserClassInfo.php");
require_once("UserClassFieldInfo.php");
require_once("UserRelationInfo.php");

class UserSettings
{
    public $DatabaseConnections = array();
    public $DataModels = array();


    public function GetConnection($id)
    {
    	foreach ($this->DatabaseConnections as $connection)
    	{
    		if ($connection->Id == $id)
    			return $connection;
    	}
		
    	return null;
    }

    public function Load($fileName)
    {
	    $xmlDocument = new DOMDocument();
		$xmlDocument->load($fileName);
		set_time_limit(0);
		$xpath = new DOMXPath($xmlDocument);
//		set_time_limit(30);

		$queryConnection = "/settings/connections/connection";
		$entries = $xpath->query($queryConnection);

        foreach ($entries as $xmlElement)
        {
            $databaseConnectionInfo = new DatabaseConnectionInfo();
            $connection = $databaseConnectionInfo->Connection;

            $databaseConnectionInfo->Id = $xmlElement->getAttribute("id");
            $connection->HostName = $xmlElement->getAttribute("hostname");

            if($xmlElement->getAttribute("port") != null)
				$connection->Port = (int) $xmlElement->getAttribute("port");

            $connection->UserName = $xmlElement->getAttribute("username");
            $connection->Password = $xmlElement->getAttribute("password");
            $connection->Type = new ServerType((int) $xmlElement->getAttribute("type"));

            $databaseConnectionInfo->DatabaseName = $xmlElement->getAttribute("database");
         
            $this->DatabaseConnections[] = $databaseConnectionInfo;
        }

		$queryDataModel = "/settings/datamodels/datamodel";
		$entries = $xpath->query($queryDataModel);        

        foreach ($entries as $xmlElement)
        {
            $userDataModel = new UserDataModel();

            $userDataModel->Name = $xmlElement->getAttribute("name");
            $userDataModel->ServerNamespace = $xmlElement->getAttribute("servernamespace");
            $userDataModel->ClientNamespace = $xmlElement->getAttribute("clientnamespace");
            $userDataModel->ServerLanguage = $xmlElement->getAttribute("serverlanguage");
            try
            {
                $userDataModel->IsGenerated = (boolean) $xmlElement->getAttribute("generated");
                $userDataModel->IsGenerateTestDrive = $xmlElement->getAttribute("generateTestDrive") == "true" ? true : false;
                $userDataModel->IsGenerateUnitTests = $xmlElement->getAttribute("generateUnitTests") == "true" ? true : false;
                $userDataModel->LOC = (int) $xmlElement->getAttribute("loc");
            	$userDataModel->Build = (int) $xmlElement->getAttribute("build");
            }
            catch(Exception $e)
            {
            	Log::logException(LoggingConstants::EXCEPTION, "unable to load config", $e);
            }

			$xmlTables = $xmlElement->getElementsByTagName("tables");
			if ($xmlTables->length > 0 )
			{
				foreach ($xmlTables->item(0)->getElementsByTagName("table") as $xmlElementUserTableInfo)
				{
				    $tableInfo = new UserTableInfo();
				
				    $tableInfo->Name = $xmlElementUserTableInfo->getAttribute("name");
				    $tableInfo->SchemaName = $xmlElementUserTableInfo->getAttribute("schema");
				    $tableInfo->DatabaseConnectionInfoId = $xmlElementUserTableInfo->getAttribute("connection");
				
				    $userDataModel->Tables[] = $tableInfo;
				}				
			}


			$xmlMapping = $xmlElement->getElementsByTagName("mapping");
			if ($xmlMapping->length > 0 )
			{
				foreach ($xmlMapping->item(0)->getElementsByTagName("table") as $xmlElementUserClassInfo)
	            {
	                $userClassInfo = new UserClassInfo();
	                $userClassInfo->Name = $xmlElementUserClassInfo->getAttribute("alias");
	                $userClassInfo->TableName = $xmlElementUserClassInfo->getAttribute("name");
	                $userClassInfo->DatabaseConnectionInfoId = $xmlElementUserClassInfo->getAttribute("connection");
					
	                $xmlColumns = $xmlElementUserClassInfo->getElementsByTagName("columns");
	                
	                foreach ($xmlColumns->item(0)->getElementsByTagName("column") as $xmlNodeColumn)
	                {
	                    $fieldInfo = new UserClassFieldInfo();
	
	                    $fieldInfo->ColumnName = $xmlNodeColumn->getAttribute("name");
	                    $fieldInfo->Name = $xmlNodeColumn->getAttribute("alias");
	
	                    $userClassInfo->Fields[] = $fieldInfo;
	                }
	
	                $xmlRelations = $xmlElementUserClassInfo->getElementsByTagName("relations");
	                
	                foreach ($xmlRelations->item(0)->getElementsByTagName("relation") as $xmlNodeRelation)
	                {
	                    $relationInfo = new UserRelationInfo();
	
	                    $relationInfo->ForeignKey = $xmlNodeRelation->getAttribute("name");
	                    $relationInfo->Alias = $xmlNodeRelation->getAttribute("alias");
	                    $relationInfo->Type = $xmlNodeRelation->getAttribute("type");
	
	                    $userClassInfo->Relations[] = $relationInfo;
	                }
	
	                $userDataModel->UserClasses[] = $userClassInfo;
	            }
			}
			
        	if(count($userDataModel->Tables)>0)
    		{
	    		$connectionId = $userDataModel->Tables[0]->DatabaseConnectionInfoId;
	    		$userDataModel->setServerConnection($this->GetConnection($connectionId));
    		}

            $this->DataModels[] = $userDataModel;
        }
    }

    public function Save($fileName)
    {    
	    $xmlDocument = new DOMDocument('1.0', 'utf-8');

	    $xmlSettings = $xmlDocument->createElement("settings");
		$xmlConnections = $xmlDocument->createElement("connections");
		
		foreach ($this->DatabaseConnections as $connection)
		{
		    $xmlConnection = $xmlDocument->createElement("connection");
		
		    $xmlConnection->setAttribute("id", $connection->Id);
			$xmlConnection->setAttribute("database", $connection->DatabaseName);
			$xmlConnection->setAttribute("hostname", $connection->Connection->HostName);
			$xmlConnection->setAttribute("username", $connection->Connection->UserName);
			$xmlConnection->setAttribute("password", $connection->Connection->Password);
			$xmlConnection->setAttribute("type", $connection->Connection->Type->Id);
			if ($connection->Connection->Port != null)
				$xmlConnection->setAttribute("port", $connection->Connection->Port);
		
			$xmlConnections->appendChild($xmlConnection);
		}
		
		$xmlSettings->appendChild($xmlConnections);
        $xmlDocument->appendChild($xmlSettings);


		$xmlDataModels = $xmlDocument->createElement("datamodels");
        
		foreach ($this->DataModels as $dataModel)
		{
			$xmlDataModel = $xmlDocument->createElement("datamodel");
			
			$xmlDataModel->setAttribute("name", $dataModel->Name);
		    $xmlDataModel->setAttribute("servernamespace", $dataModel->ServerNamespace);
		    $xmlDataModel->setAttribute("clientnamespace", $dataModel->ClientNamespace);
		    $xmlDataModel->setAttribute("serverlanguage", $dataModel->ServerLanguage);
		    $xmlDataModel->setAttribute("build", $dataModel->Build);
		    $xmlDataModel->setAttribute("generated", $dataModel->IsGenerated == true ? "true" : "false");
		    $xmlDataModel->setAttribute("generateTestDrive", $dataModel->IsGenerateTestDrive == true ? "true" : "false");
		    $xmlDataModel->setAttribute("generateUnitTests", $dataModel->IsGenerateUnitTests == true ? "true" : "false");
		    $xmlDataModel->setAttribute("loc", $dataModel->LOC);

		    $xmlTables = $xmlDocument->createElement("tables");
		   
		    foreach ($dataModel->Tables as $tableInfo)
		    {
		    	$xmlTable = $xmlDocument->createElement("table");
		
		        $xmlTable->setAttribute("name", $tableInfo->Name);
		        $xmlTable->setAttribute("schema", $tableInfo->SchemaName);
		        $xmlTable->setAttribute("connection", $tableInfo->DatabaseConnectionInfoId);

		        $xmlTables->appendChild($xmlTable);
		    }
			$xmlDataModel->appendChild($xmlTables);
			

			$xmlMapping = $xmlDocument->createElement("mapping");
		    foreach ($dataModel->UserClasses as $classInfo)
		    {
		    	$xmlTable = $xmlDocument->createElement("table");
		    	
		        $xmlTable->setAttribute("name", $classInfo->TableName);
		        $xmlTable->setAttribute("alias", $classInfo->Name);
		        $xmlTable->setAttribute("connection", $classInfo->DatabaseConnectionInfoId);
		
		        $xmlColumns = $xmlDocument->createElement("columns");
		
		        foreach ($classInfo->Fields as $fieldInfo)
		        {
		            if ($fieldInfo->Name == $fieldInfo->ColumnName)
		                continue;
		
		            $xmlColumn = $xmlDocument->createElement("column");
		            $xmlColumn->setAttribute("name", $fieldInfo->ColumnName);
		            $xmlColumn->setAttribute("alias", $fieldInfo->Name);
		
		            $xmlColumns->appendChild($xmlColumn);
		        }
				
		        $xmlTable->appendChild($xmlColumns);
		
		        $xmlRelations = $xmlDocument->createElement("relations");
		
		        foreach ($classInfo->Relations as $relationInfo)
		        {
		            $xmlRelation = $xmlDocument->createElement("relation");
		            $xmlRelation->setAttribute("name", $relationInfo->ForeignKey);
		            $xmlRelation->setAttribute("type", $relationInfo->Type);
		            $xmlRelation->setAttribute("alias", $relationInfo->Alias);
		            
		            $xmlRelations->appendChild($xmlRelation);
		        }
		
		        $xmlTable->appendChild($xmlRelations);
		        $xmlMapping->appendChild($xmlTable);
		    }
		    
			$xmlDataModel->appendChild($xmlMapping);
			
			$xmlDataModels->appendChild($xmlDataModel);
			
            $xmlSettings->appendChild($xmlDataModels);
		}
		
		$xmlDocument->save($fileName);
    }

    public function GetModel($modelName)
    {
    	foreach($this->DataModels as $item)
    	{
    		if($item->Name == $modelName)
    		{
    			return $item;
    		}
    			
    	}
    	
    	return null;
    }
}
?>