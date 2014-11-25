<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLSaveCodegenerator.php
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
class WDMServerPHPDataMsSQLSaveCodegenerator extends WDMCodegenerator
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
		$this->varName = $this->meta->getFunctionParameter($this->tableName);
		$table = $this->tableName;
		$this->writeText("
			public function save(DomainObject \$".$this->varName.")
			{
				if (\$this->exists(\$".$this->varName."))
					return \$this->update(\$".$this->varName.");
				return \$this->create(\$".$this->varName.");
			}
		");

		$this->writeText("
			public function exists(DomainObject \$".$this->varName.")
			{
				\$link = \$this->getConnection();
		");

		// generate Insert statement
		$columnsCount = 0;
		$columnsSize = count($this->tableMeta->Columns);

		$this->writeText("
				\$sql = \"Select * From [".$this->tableName."] Where ");
		$whereString = "";
		$andString = ".\" AND ";
		foreach ($this->tableMeta->Columns as $column)
		{
			if ($column->keyType == ColumnKeyType::PRIMARY)
			{
				$whereString = $whereString."[".$column->name."]=\".\$this->getSafeParam(\$".$this->varName."->get".$this->meta->getPropertyName($table,$column->name)."(),\"".$column->isNullable."\",\$link)".$andString;
			}
		}
		if (strlen($whereString) > 0)
			$this->writeText(substr($whereString,0,strlen($whereString)-strlen($andString)).";");

		$this->writeText("
				\$result = mssql_query(\$sql);

				\$numrows = mssql_num_rows(\$result);

				return \$numrows > 0;
		");

		$this->writeText("
			}");
		
	}
}
?>