<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLFindByPrimaryKeyCodegenerator.php
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
class WDMServerPHPDataMySQLFindByPrimaryKeyCodegenerator extends WDMCodegenerator
{
	protected $tableName;
	protected $tableMeta;

	public function __construct($tableName)
	{
		$this->tableName = $tableName;
	}
	protected function doGenerate()
	{
		$this->tableMeta = $this->meta->tables[$this->tableName];
		$this->className = $this->meta->getFunctionParameter($this->tableName);
		$table = $this->tableName;
		$this->writeText("
			public function findByPrimaryKey(");
		
		$keys = ""; 
		$whereString = ""; 
		$comma = ", ";
		$andString = ".\" AND ";
		foreach ($this->tableMeta->Columns as $column)
		{
			if ($column->keyType == ColumnKeyType::PRIMARY)
			{
				$keys = $keys."$".$this->meta->getFunctionParameter($this->meta->getPropertyName($table,$column->name)).$comma;
				$whereString = $whereString."`".$column->name."`=\".\$this->getSafeParam(\$".$this->meta->getFunctionParameter($this->meta->getPropertyName($table,$column->name)).",\"".$column->isNullable."\",\$link)".$andString;
			}
		}
		if (strlen($keys) > 0)
			$this->writeText(substr($keys,0,strlen($keys)-strlen($comma)).")");
		if (strlen($whereString) > 0)
			$whereString = substr($whereString,0,strlen($whereString)-strlen($andString));
			
		// generate database connection
    	$this->writeText("
			{	
				\$link = \$this->getConnection();
				\$sql = \"Select * From `".$this->tableName."` Where ".$whereString.";");

		$this->writeText("
				\$result = mysql_query(\$sql);
				\$row = mysql_fetch_assoc(\$result);
				mysql_close(\$link);

				return \$this->doLoad(\$row);
		");

		$this->writeText("
			}");
		
	}	
}
?>