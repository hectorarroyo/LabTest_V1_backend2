<?php
/*******************************************************************
 * WDMServerPHPDataCodegenerator.php
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
require_once("MSSQL/WDMServerPHPDataMsSQLCodegenerator.php");
require_once("MySQL/WDMServerPHPDataMySQLCodegenerator.php");
class WDMServerPHPDataCodegenerator extends WDMCodegenerator
{
	protected $tablePrefix;
	protected $tableName;

	public function __construct($tablePrefix,$tableName)
	{
		$this->tablePrefix = $tablePrefix;
		$this->tableName = $tableName;
	}

	protected function doGenerate()
	{
		if ($this->meta->getDbType() == DatabaseInfoType::MSSQL)
			$this->generatePart(new WDMServerPHPDataMsSQLCodegenerator($this->tablePrefix,$this->tableName));
		elseif ($this->meta->getDbType() == DatabaseInfoType::MYSQL)
			$this->generatePart(new WDMServerPHPDataMySQLCodegenerator($this->tablePrefix,$this->tableName));
	}
}
?>