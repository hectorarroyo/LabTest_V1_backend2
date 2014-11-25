<?php
/*******************************************************************
 * WDMMetaInspector.php
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

require_once(WebOrbServicesPath. "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "DataManagement" . DIRECTORY_SEPARATOR . "DataManagementService.php");

abstract class WDMMetaInspector
{
        protected $dbType = array();
        protected $asType = array();
        protected $nullableTypes = array();
        protected $binaryTypes = array();
		protected $editableTypes = array();

        protected $nameMapper;
		protected $currentDatabase;

		protected $userDataModel;
		protected $dbInspector;

		public $tables;
		public /*DatabaseConnectionInfo*/ $database;
		public /*array StoredProcedureInfo*/ $storedProcedures;

		protected function setTables()
		{
			$this->tables = array();
			foreach($this->userDataModel->Tables as $table)
			{
				$this->tables[$table->Name] = DataManagementService::GetTableMeta($table->DatabaseConnectionInfoId, $table->SchemaName,  $table->Name);
			}
		}

        public /*void*/ function initialize(UserDataModel $userDataModel, INameMapper $nameMapper)
        {
        	$this->userDataModel = $userDataModel;
        	$this->dbType = $userDataModel->getServerConnection()->Connection->Type->Id;
        	$this->nameMapper = $nameMapper;
        	$this->setTables();
        	$this->setCurrentDatabase($userDataModel->getServerConnection()->getDataBaseName());
        }

        public /*bool*/ function IsNullable(/*string*/ $dataType)
        {
            return array_key_exists($dataType, $this->nullableTypes);
        }
		public function getDbType()
		{
			return $this->dbType;
		}
        public /*void*/ function setCurrentDatabase(/*string*/ $databaseName)
        {
            $this->currentDatabase = $databaseName;
        }

        public /*string*/ function getCurrentDatabase()
        {
        	return $this->currentDatabase;
        }

        public /*string*/ function getUserDataModel()
        {
            return $this->userDataModel;
        }

        public /*string*/ abstract function getPrimitiveValue(/*string*/ $dataType);

        public /*bool*/ function IsEditable(/*string*/ $dataType)
        {
            return in_array($dataType, $this->editableTypes);
        }

        public /*bool*/ function IsBinary(/*string*/ $type)
        {
            return in_array($type, $this->binaryTypes);
        }

        public /*string*/ function getStoredProcedureName(/*string*/ $name)
        {
            return DefaultNameMapper::getSafeName($name);
        }

        public /*string*/ function getPropertyName(/*string*/ $table, /*string*/ $column)
        {
            return $this->nameMapper->getPropertyName($this->currentDatabase, $table, $column);
        }

        public /*string*/ function getProperty(/*string*/ $table, /*string*/ $column)
        {
            return $this->getPropertyName($table, $column);
        }

        public /*string*/ function getParentProperty(/*string*/ $table, /*string*/ $parentTable, /*string*/ $key, /*bool*/ $lcase)
        {
            $propertyName = $this->nameMapper->getParentProperty($this->currentDatabase, $table, $parentTable, $key);

            if($lcase)
                return strtolower(substr($propertyName,0 , 1)).substr($propertyName, 1);

            return $propertyName;
        }

        public /*string*/ function getChildProperty(/*string*/ $table, /*string*/ $childTable,/*string*/ $key, /*bool*/ $lcase)
        {
            $propertyName = $this->nameMapper->getChildProperty($this->currentDatabase, $table, $childTable, $key);

            if ($lcase)
                return strtolower(substr($propertyName,0 , 1)).substr($propertyName, 1);

            return $propertyName;
        }

        public /*string*/ function getFunctionParameter(/*string*/ $columnName)
        {
            $variableName = str_replace(' ', '_', $columnName);
            $variableName = str_replace('-', '_', $variableName);
            $variableName = str_replace('.', '_', $variableName);

            return strtolower(substr($variableName,0 , 1)).substr($variableName, 1);
        }

        public /*string*/ function getClassName(/*string*/ $table)
        {
       		return $this->nameMapper->getClassName($table);

        }

        public /*string*/ function getASDataType(/*string*/ $xsDataType)
        {
            return $this->asType[$xsDataType];
        }

        public /*string*/ function getServerNamespace()
        {
            return $this->userDataModel->ServerNamespace;
        }

        public /*string*/ function getServerFileName()
        {
            return $this->userDataModel->ServerFileName;
        }

        public /*string*/ function getServerLanguage()
        {
            return $this->userDataModel->ServerLanguage;
        }

        public /*bool*/ function IsGenEmptyClass()
        {
            return $this->userDataModel->GenerateEmptyClass;
        }

        public /*bool*/ function IsGenerateTestDrive()
        {
            return $this->userDataModel->IsGenerateTestDrive;
        }

        public /*bool*/ function IsGenerateUnitTests()
        {
            return $this->userDataModel->IsGenerateUnitTests;
        }

        public /*string*/ function getDatabaseServerType(/*string*/ $databaseName)
        {
            return $this->userDataModel->Databases[$databaseName]->ServerType;
        }

        public /*string*/ function getClientNamespace()
        {
            return $this->userDataModel->ClientNamespace;
        }

        public /*string*/ function getClientFolder()
        {
            return str_replace('.', DIRECTORY_SEPARATOR, $this->getClientNamespace());
        }

        public /*string*/ function getServerFolder()
        {
            return str_replace('.', DIRECTORY_SEPARATOR, $this->getServerNamespace());
        }

        public function getModelConection()
        {
        	return $this->database;
        }
}
?>