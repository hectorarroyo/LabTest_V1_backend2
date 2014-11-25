<?php
/*******************************************************************
 * DataManagementService.php
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
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/DatabaseInfoType.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/RelationType.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/ColumnKeyType.php");
require_once("ServerConnectionInfo.php");
require_once(WebOrb . "Util/Paths.php");
require_once("UserSettings.php");
require_once("UserTableInfo.php");
require_once("TableMeta.php");
require_once("ServerCodeLanguage.php");
require_once(WebOrb . "V3Types/AckMessage.php");
require_once("UserDataModel.php");
require_once(WebOrbServicesPath . "Weborb/Management/Codegen/DefaultNameMapper.php");
require_once(WebOrbServicesPath . "Weborb/Management/Codegen/WDM/import.php");
require_once(WebOrbServicesPath . "Weborb/Management/Codegen/NameMapper.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "ZIP" . DIRECTORY_SEPARATOR . "pclzip.lib.php");

class DataManagementService
{
	public function GetDatabaseTypes()
	{
	    $list = array();

	    $list[] = new ServerType(DatabaseInfoType::MYSQL);
		$list[] = new ServerType(DatabaseInfoType::MSSQL);

	    return $list;
	}

	public function GetDatabases(ServerConnectionInfo $connectionInfo)
	{
	    return $connectionInfo->GetInspector()->GetDatabases();
	}

	public function GetDataModels()
	{
		$models = array();

		$dataModels = self::GetSettings()->DataModels;
		foreach ($dataModels as $userDataModel)
		{
		    $userDataModel->Tables = array();
		    $models[] = $userDataModel;
		}

		return $models;
	}

	public function SaveDataModel(UserDataModel $model, $create)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = $userSettings->GetModel($model->Name);

	    if($userDataModel == null)
	    {
	        $userDataModel = new UserDataModel();
	        $userDataModel->Name = $model->Name;
	        $userSettings->DataModels[] = $userDataModel;
	    }
	    else if ($create)
                throw new Exception("Data model with name \"".$model->Name."\" already exists.");

	    $userDataModel->ClientNamespace = $model->ClientNamespace;
	    $userDataModel->IsGenerateTestDrive = $model->IsGenerateTestDrive;
	    $userDataModel->IsGenerateUnitTests = $model->IsGenerateUnitTests;
	    $userDataModel->ServerLanguage = $model->ServerLanguage;
	    $userDataModel->ServerNamespace = $model->ServerNamespace;

	    $userSettings->Save(Paths::getWDMConfigPath());

	    return $userDataModel;
	}

	public function GetConnections()
	{
		return self::GetSettings()->DatabaseConnections;
	}

	public static function GetSettings()
	{
	    $userSettings = new UserSettings();

	    $settingsFilePath = Paths::getWDMConfigPath();

	    if (file_exists($settingsFilePath))
	        $userSettings->Load($settingsFilePath);

	    return $userSettings;
	}

	public function GetTables($connectionId)
	{
	    $tableInfoList = array();
	    $databaseConnection = self::GetSettings()->GetConnection($connectionId);

	    foreach ($databaseConnection->Connection->GetInspector()->GetTables($databaseConnection->DatabaseName) as $tableInfo)
	    {
	    	$tableInfoList[] = new UserTableInfo($tableInfo->SchemaName, $tableInfo->Name, false, $databaseConnection->Id);
	    }

	    return $tableInfoList;
	}

	public static function GetTableMeta($connectionId, $schema, $table)
	{
	    $tableMeta = new TableMeta();
	    $databaseConnection = self::GetSettings()->GetConnection($connectionId);
	    $inspector = $databaseConnection->Connection->GetInspector();

	    $tableMeta->Columns = array_merge($tableMeta->Columns, $inspector->GetColumns($databaseConnection->DatabaseName, $schema, $table));
		$tableMeta->Relations = array_merge($tableMeta->Relations, $inspector->getRelations($databaseConnection->DatabaseName, $table, RelationType::Parent));
		$tableMeta->Relations = array_merge($tableMeta->Relations, $inspector->getRelations($databaseConnection->DatabaseName, $table, RelationType::Child));

	    $pkColumns = array();

	    foreach ($tableMeta->Columns as $columnInfo)
	        if($columnInfo->keyType == ColumnKeyType::PRIMARY)
	            $pkColumns[] = $columnInfo->name;

	    foreach ($tableMeta->Relations as $relationInfo)
	    {
	        if ($relationInfo->Type == RelationType::Parent)
	        {
	            $columnsIncludedInPrimaryKey = 0;

	            foreach ($relationInfo->Columns as $relationColumnInfo)
	            {
	                if (in_array($relationColumnInfo->ColumnName, $pkColumns))
	                    $columnsIncludedInPrimaryKey++;
	            }

	            if (count($pkColumns) > 0 && $columnsIncludedInPrimaryKey == count($pkColumns))
	                $relationInfo->IsOneToMany = false;
	            else
	                $relationInfo->IsOneToMany = true;
	        }
	        else
	        {
	            $pkColumnsRelation = array();
	            $columnsIncludedInPrimaryKey = 0;

	            foreach ($inspector->GetColumns($databaseConnection->DatabaseName, $relationInfo->RelatedSchemaName, $relationInfo->RelatedTableName) as $columnInfo)
	                if ($columnInfo->keyType == ColumnKeyType::PRIMARY)
	                    $pkColumnsRelation[] = $columnInfo->name;

	            foreach ($relationInfo->Columns as $relationColumnInfo)
	            {
	                if (in_array($relationColumnInfo->RelatedColumnName, $pkColumnsRelation))
	                    $columnsIncludedInPrimaryKey++;
	            }

	            if (count($pkColumnsRelation) > 0 && $columnsIncludedInPrimaryKey == count($pkColumnsRelation))
	                $relationInfo->IsOneToMany = false;
	            else
	                $relationInfo->IsOneToMany = true;
	        }
	    }
	    return $tableMeta;
	}

	public function CreateDatabaseConnection(ServerConnectionInfo $connectionInfo, $database)
	{
	    $databaseConnectionInfo = new DatabaseConnectionInfo();
	    $databaseConnectionInfo->Connection = $connectionInfo;
	    $databaseConnectionInfo->DatabaseName = $database;
	    $databaseConnectionInfo->Id = AckMessage::uuid();

	    $userSettings = self::GetSettings();
	    $userSettings->DatabaseConnections[] = $databaseConnectionInfo;
	    $userSettings->Save(Paths::getWDMConfigPath());

	    return $databaseConnectionInfo;
	}

	public function RemoveConnection($connectionId)
	{
	    $userSettings = self::GetSettings();
	    $connection = $userSettings->GetConnection($connectionId);

	    foreach ($userSettings->DataModels as $model)
	    {
	    	$tableInfo = null;

	    	foreach ($model->Tables as $compareTableInfo)
	    	{
	    		if ($compareTableInfo->DatabaseConnectionInfoId == $connectionId)
	    			$tableInfo = $compareTableInfo;
	    	}

	        if ($tableInfo != null)
	        {
	            throw new Exception("Connection to database \"".$connection->DatabaseName."\" can't be removed because it used in \"".$model->Name."\" Model (at least in table \"".$tableInfo->Name."\")");
	        }
	    }

		$this->removeItemFromArray($connection, $userSettings->DatabaseConnections);

	    $userSettings->Save(Paths::getWDMConfigPath());
	}

	private function removeItemFromArray($val,&$arr)
	{
		$i = array_search($val,$arr);
		if ($i === false) return false;
		$arr=array_merge(array_slice($arr, 0,$i), array_slice($arr, $i+1));

		return true;
	}

	public function AddTableToDataModel($modelName, $schemaName, $tableName, $connectionId, $view)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = null;

	    foreach($userSettings->DataModels as $item)
	    {
	    	if ($item->Name == $modelName)
	    		$userDataModel = $item;
	    }

	    if ($userDataModel == null)
	        throw new Exception("Data model with name '" . $modelName . "' not found");

	    $table = null;

	    foreach($userDataModel->Tables as $tableInfo)
	    {
	    	if ($tableName == $tableInfo->Name && $connectionId == $tableInfo->DatabaseConnectionInfoId)
	    		$table = $tableInfo;
	    }

	    if ($table != null)
	    	throw new Exception("Data model name '".$modelName."' already contains table '".$tableName."'");

	    $userDataModel->Tables[] = new UserTableInfo($schemaName, $tableName, $view, $connectionId);

	    $userClassInfo = new UserClassInfo();

	    $userClassInfo->DatabaseConnectionInfoId = $connectionId;
	    $userClassInfo->TableName = $tableName;
	    $userClassInfo->Name = DefaultNameMapper::getSafeName($tableName);

	    $userDataModel->UserClasses[] = $userClassInfo;

	    $tableMeta = self::GetTableMeta($connectionId, $schemaName, $tableName);

	    foreach ($tableMeta->Columns as $columnInfo)
	    {
	        if (!DefaultNameMapper::isNameSafe($columnInfo->name))
	        {
	            $userClassFieldInfo = new UserClassFieldInfo();

	            $userClassFieldInfo->ColumnName = $columnInfo->name;
	            $userClassFieldInfo->Name = DefaultNameMapper::getSafeName($columnInfo->name);

	            $userClassInfo->Fields[] = $userClassFieldInfo;
	        }
	    }

	    foreach ($tableMeta->Relations as $relationInfo)
	    {
	        if (!DefaultNameMapper::isNameSafe($relationInfo->RelatedTableName))
	        {
	            $userRelationInfo = new UserRelationInfo();

	            $userRelationInfo->Alias = "Related" . DefaultNameMapper::getSafeName($relationInfo->RelatedTableName);
	            $userRelationInfo->ForeignKey = $relationInfo->ForeignKey;
	            $userRelationInfo->Type = $relationInfo->Type;

	            $userClassInfo->Relations[] = $userRelationInfo;
	        }
	    }

	    $userSettings->Save(Paths::getWDMConfigPath());

	    return $userClassInfo;
	}

	public function GetDataModelTables($name)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = null;
	    foreach ($userSettings->DataModels as $item)
	    {
	    	if ($item->Name == $name)
	    		$userDataModel = $item;
	    }

	    if ($userDataModel == null)
	        throw new Exception("Data model with name '" . $name. "' not found");

	    //check for table model existance

	    $removedTables = array();

	    foreach ($userDataModel->Tables as $tableInfo)
	    {
	        $connection = $userSettings->GetConnection($tableInfo->DatabaseConnectionInfoId);

	        $inspector = $connection->Connection->GetInspector();

	        if (!$inspector->IsTableExists($connection->DatabaseName, $tableInfo))
	            $removedTables[] = $tableInfo;
	    }

	    if (count($removedTables) > 0)
	    {
	        foreach ($removedTables as $tableInfo)
	            $this->removeItemFromArray($tableInfo,$userDataModel->Tables);

	        $userSettings->Save(Paths::getWDMConfigPath());
	    }

	    return $userDataModel->Tables;
	}

	public function UpdateUserModel(UserDataModel $model)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = $userSettings->GetModel($model->Name);

	    $userDataModel->ClientNamespace = $model->ClientNamespace;
	    $userDataModel->ServerNamespace = $model->ServerNamespace;

	    foreach ($model->UserClasses as $userClass)
	    {
	        $userDataModel->UpdateClassInfo($userClass);
	    }

	    $userSettings->Save(Paths::getWDMConfigPath());
	}

	public function RemoveModel($modelName)
	{
	    $userSettings = self::GetSettings();
	    $model = $userSettings->GetModel($modelName);

	    $this->removeItemFromArray($model,$userSettings->DataModels);

	    $userSettings->Save(Paths::getWDMConfigPath());
	}

	public function GetData($connectionId, $schema, $table)
	{
	    $databaseConnection = self::GetSettings()->GetConnection($connectionId);

	    return $databaseConnection->Connection->GetInspector()->GetData($databaseConnection->DatabaseName,
	        new TableInfo($table,$schema), 100);
	}

	public function RemoveDataModelTable($modelName, $tableName, $connectionId)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = $userSettings->GetModel($modelName);

	    if ($userDataModel == null)
	        throw new Exception("Data model with name '" . $modelName . "' not found");

	    $tableInfo = null;
	    foreach ($userDataModel->Tables as $compareTableInfo)
	    {
	    	if ($tableName == $compareTableInfo->Name && $connectionId == $compareTableInfo->DatabaseConnectionInfoId)
	    		$tableInfo = $compareTableInfo;
	    }

		$this->removeItemFromArray($tableInfo, $userDataModel->Tables);

	    $userSettings->Save(Paths::getWDMConfigPath());
	}

	public function UpdateUserClass($modelName, UserClassInfo $classInfo)
	{
	   $userSettings = self::GetSettings();
	   $userDataModel = $userSettings->GetModel($modelName);

	   $userDataModel->UpdateClassInfo($classInfo);

	   $userSettings->Save(Paths::getWDMConfigPath());
	}

	public function FindOrCreateModel($modelName)
	{
	    $models = $this->GetDataModels();

	    $userDataModel = null;
	    foreach ($models as $item)
	    {
	    	if ($item->Name == $modelName)
	    		$userDataModel = $item;
	    }

		if ($userDataModel == null)
		{
			$userDataModel = new UserDataModel();
			$userDataModel->Name = $modelName;
			$userDataModel->ServerLanguage = ServerCodeLanguage::PHP;

			$this->SaveDataModel($userDataModel, true);
		}

	    return $userDataModel;
	}

	public function CreateDataModel($name)
	{
	    $userSettings = self::GetSettings();

	    foreach ($userSettings->DataModels as $item)
	    {
	    	if ($item->Name == $name)
	    		throw new Exception("Data model with name '".$name."' already exists");
	    }

	    $userDataModel = new UserDataModel();
	    $userDataModel->Name = $name;
	    $userDataModel->ServerLanguage = "php";

	    $userSettings->DataModels[] = $userDataModel;
	    $userSettings->Save(Paths::getWDMConfigPath());

	    return $userDataModel;
	}

	public function GetServerLanguages()
	{
	    $languages = array();

	    $languages[] = new ServerCodeLanguage("php", "PHP");

	    return $languages;
	}

	public function Generate($modelName)
	{
	    $userSettings = self::GetSettings();
	    $userDataModel = null;

	    foreach($userSettings->DataModels as $item)
	    {
	    	if ($item->Name == $modelName)
	    		$userDataModel = $item;
	    }

	    if ($userDataModel == null)
	        throw new Exception("Data model with name '" . $modelName . "' not found");

		$tables = array();

	    foreach ($userDataModel->Tables as $tableInfo)
	    {
	    	$tables[$tableInfo->Name] = self::GetTableMeta($tableInfo->DatabaseConnectionInfoId, $tableInfo->SchemaName, $tableInfo->Name);
	    	$databaseConnectionInfoId = $tableInfo->DatabaseConnectionInfoId;
	    }

	    $databaseConnectionInfo = $userSettings->GetConnection($databaseConnectionInfoId);

	    $meta = WDMMetaInspectorFactory::getInspector($databaseConnectionInfo->Connection->Type);
	    $meta->tables = $tables;
	    $meta->database = $databaseConnectionInfo;
   	    $meta->setCurrentDatabase($meta->database->DatabaseName);
	    $meta->storedProcedures = $meta->database->Connection->GetInspector()->getStoredProcedures($meta->database->DatabaseName);

		$codegen = new WDMCodegenerator();
		$nameMapper = new NameMapper($userSettings, $userDataModel);

		$meta->initialize($userDataModel, $nameMapper);
		$codegen->setMeta($meta);
		$result = $codegen->Generate();
		CreateArc::createArchive($result, $modelName . ".zip", "weborbassets" . DIRECTORY_SEPARATOR . "wdm" . DIRECTORY_SEPARATOR . "output", "weborbassets" . DIRECTORY_SEPARATOR . "wdm" );
		//$result->saveToDirecorty("".WebOrb."weborbassets".DIRECTORY_SEPARATOR."wdm".DIRECTORY_SEPARATOR."output".DIRECTORY_SEPARATOR."".$userDataModel->Name."".DIRECTORY_SEPARATOR."");
		return $result->LineCount;
	}

	public function Deploy($modelName)
	{
		$weborbInstallDir = substr(WebOrb,0,strlen(WebOrb)-7);
		$archive = new PclZip($weborbInstallDir . "weborbassets" . DIRECTORY_SEPARATOR . "wdm" . DIRECTORY_SEPARATOR . "output" . DIRECTORY_SEPARATOR . $modelName . ".zip");
		$archive->extract(PCLZIP_OPT_PATH, WebOrbServicesPath, PCLZIP_OPT_BY_EREG, "server", PCLZIP_OPT_REMOVE_PATH, "server");
	}
}
?>