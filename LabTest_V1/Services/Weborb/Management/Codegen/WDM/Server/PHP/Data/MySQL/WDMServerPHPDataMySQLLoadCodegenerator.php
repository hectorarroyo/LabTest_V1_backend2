<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLLoadCodegenerator.php
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
class WDMServerPHPDataMySQLLoadCodegenerator extends WDMCodegenerator
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
			public function doLoad(&\$objectRow)
			{");
		
    	$this->writeText("
				$".$this->varName." = new ".$this->meta->getClassName($this->tableName)."();");
		foreach ($this->tableMeta->Columns as $column)
		{
			$this->writeText("
  				\$".$this->varName."->set".$this->meta->getPropertyName($table,$column->name)."(\$objectRow[\"".$column->name."\"]);");
		}
		
		$this->writeText("
				return \$".$this->varName.";
			}");
		
	}
}
?>