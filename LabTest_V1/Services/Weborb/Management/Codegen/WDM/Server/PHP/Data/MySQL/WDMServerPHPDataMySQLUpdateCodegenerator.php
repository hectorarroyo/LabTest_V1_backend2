<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLUpdateCodegenerator.php
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
class WDMServerPHPDataMySQLUpdateCodegenerator extends WDMCodegenerator
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
			public function update(DomainObject \$".$this->varName.")
			{
				\$link = \$this->getConnection();
		");
		
		// generate Insert statement
		$columnsCount = 0;
		$columnsSize = count($this->tableMeta->Columns);

		$this->writeText("
				\$sql = \"Update `".$this->tableName."` Set ");
		foreach ($this->tableMeta->Columns as $column)
		{
			$columnsCount = $columnsCount + 1;
			if ($column->isAutoIncrement != true)
			{
				if ($column->dataTypeInfo->dataType == "datetime")
				{
					$this->writeText("
	         			`".$column->name."`=\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."()->getDateTime(),\"".$column->isNullable."\",\$link).\"");					
				}
				else 
				{
					$this->writeText("
	         			`".$column->name."`=\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."(),\"".$column->isNullable."\",\$link).\"");
				}
				if ($columnsCount < $columnsSize)
					$this->writeText(",");
			}
		}
		$this->writeText("
				Where ");
		$whereString = "";
		$andString = ".\" AND ";
		foreach ($this->tableMeta->Columns as $column)
		{
			if ($column->keyType == ColumnKeyType::PRIMARY)
			{
				if ($column->dataTypeInfo->dataType == "datetime")
				{
					$whereString = $whereString."`".$column->name."`=\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."()->getDateTime(),\"".$column->isNullable."\",\$link)".$andString;
				}
				else 
				{
					$whereString = $whereString."`".$column->name."`=\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."(),\"".$column->isNullable."\",\$link)".$andString;
				}
			}
		}
		if (strlen($whereString) > 0)
			$this->writeText(substr($whereString,0,strlen($whereString)-strlen($andString)).";");
		
		$this->writeText("
				mysql_query(\$sql);
          		mysql_close(\$link);
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
					foreach (\$".$this->varName."->get".$relatedName."() as \$childObject)
						\$dataMapper->update(\$childObject);
				}
			");
		}
		
				
		$this->writeText("
			return \$".$this->varName.";
			}");
		
	}
}
?>