<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLCreateCodegenerator.php
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
class WDMServerPHPDataMsSQLCreateCodegenerator extends WDMCodegenerator
{
	protected $tableName;
	protected $tableMeta;

	public function __construct($tableName)
	{
		$this->tableName = $tableName;
	}
	protected function doGenerate()
	{
		$table = $this->tableName;
		$this->tableMeta = $this->meta->tables[$this->tableName];
		$this->varName = $this->meta->getFunctionParameter($this->tableName);
		
		$this->writeText("
			public function create(DomainObject \$".$this->varName.")
			{
				\$link = \$this->getConnection();
		");

		// generate Insert statement
		$columnsCount = 0;
		$columnsSize = count($this->tableMeta->Columns);

		$this->writeText("
				\$sql = \"Insert Into [".$this->tableName."] (");
		foreach ($this->tableMeta->Columns as $column)
		{
			$columnsCount = $columnsCount + 1;
			if ($column->isAutoIncrement != true)
			{
				$this->writeText("
	         			[".$column->name."]");
				if ($columnsCount < $columnsSize)
					$this->writeText(",");
			}
		}
		$this->writeText("
				) Values (");

		$columnsCount = 0;
		$hasIdentity = false;
		foreach ($this->tableMeta->Columns as $column)
		{
			$columnsCount = $columnsCount + 1;
			if (!$column->isAutoIncrement)
			{
				if ($column->dataTypeInfo->dataType == "datetime")
				{
					$this->writeText("
         				\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."()->getDateTime(),\"".$column->isNullable."\",\$link).\"");
				}
				else 
				{
					$this->writeText("
         				\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."(),\"".$column->isNullable."\",\$link).\"");					
				}
				if ($columnsCount < $columnsSize)
					$this->writeText(",");
			}
			else
				$hasIdentity = true;
		}
		$this->writeText("
				)\";");

		// get primary key column name
		foreach ($this->tableMeta->Columns as $column)
			if ($column->keyType == ColumnKeyType::PRIMARY)
			{
				$primaryKeyName = $column->name;
				break;
			}

			
		$this->writeText("
				mssql_query(\$sql);
		");
		if ($hasIdentity)
		{
			$this->writeText("
				\$sql = \"select @@Identity as NewId\";
				\$result = mssql_query(\$sql);
 				
				\$row = mssql_fetch_assoc(\$result);
				\$".$this->varName."->set".$primaryKeyName."(\$row[\"NewId\"]);
		");		
		}	
			$this->writeText("
				mssql_close(\$link);
		");
		
		foreach ($this->tableMeta->Relations as $relation)
		if ($relation->Type == RelationType::Child)
		{
			$relatedName = $this->meta->getParentProperty($table, $relation->RelatedTableName, $relation->ForeignKey, false);
			$relatedVarName = $this->meta->getFunctionParameter($this->meta->getParentProperty($table, $relation->RelatedTableName, $relation->ForeignKey, false));
			$this->writeText("
				if (!is_null(\$".$this->varName."->get".$relatedName."()) && sizeof(\$".$this->varName."->get".$relatedName."()) > 0)
				{
					\$dataMapper = new ".$this->meta->getClassName($relation->RelatedTableName)."DataMapper();
					foreach (\$".$this->varName."->".$relatedVarName." as \$childObject)
						\$dataMapper->create(\$childObject);
				}
			");
		}
		//			foreach (\$".$this->varName."->get".$relatedName."() as \$childObject)
		//				\$dataMapper->create(\$childObject);
		
		$this->writeText("
			return \$this->registerRecord(\$".$this->varName.");"
		);

		$this->writeText("
			}");
	}
}
?>