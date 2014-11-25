<?php
/*******************************************************************
 * WDMServerPHPDataMSSQLCodegenerator.php
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
require_once("WDMServerPHPDataMsSQLCreateCodegenerator.php");
require_once("WDMServerPHPDataMsSQLFindByPrimaryKeyCodegenerator.php");
require_once("WDMServerPHPDataMsSQLLoadCodegenerator.php");
require_once("WDMServerPHPDataMsSQLRemoveCodegenerator.php");
require_once("WDMServerPHPDataMsSQLSaveCodegenerator.php");
require_once("WDMServerPHPDataMsSQLUpdateCodegenerator.php");
require_once("WDMServerPHPDataMsSQLFindAllCodegenerator.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");


class WDMServerPHPDataMsSQLCodegenerator extends WDMCodegenerator
{
	protected $tablePrefix;
	protected $tableName;
	protected $className;

	public function __construct($tablePrefix,$tableName)
	{
		$this->tablePrefix = $tablePrefix;
		$this->tableName = $tableName;
	}

	protected function doGenerate()
	{
		$this->className = $this->meta->getClassName($this->tableName);
		$this->tableMeta = $this->meta->tables[$this->tableName];
		$this->writeText("
		<?php
  		require_once(WebOrbServicesPath. \"Weborb/Data/Management/DataMapper.php\");
  		require_once(WebOrbServicesPath. \"Weborb/Data/Management/MsSqlCommandBuilder.php\");
  		require_once(WebOrbServicesPath. \"" . str_replace('.', '/', $this->meta->getServerNamespace()). "/" . $this->meta->getUserDataModel()->Name . "Config.php\");
		require_once(WebOrbServicesPath. \"" . str_replace('.', '/', $this->meta->getServerNamespace()). "/" . $this->className . ".php\");
		require_once(WebOrb.\"Util/ORBDateTime.php\");

		class ".$this->tablePrefix.$this->className."DataMapper extends DataMapper
		{
			public function getConnection()
			{
				\$database = ".$this->meta->getUserDataModel()->Name."Config::DATABASE;
				if(".$this->meta->getUserDataModel()->Name."Config::PORT != null)
					\$link = mssql_connect( ".$this->meta->getUserDataModel()->Name."Config::HOST . \":\" . ".$this->meta->getUserDataModel()->Name."Config::PORT, ".$this->meta->getUserDataModel()->Name."Config::LOGIN, ".$this->meta->getUserDataModel()->Name."Config::PASSWORD);
				else
					\$link = mssql_connect( ".$this->meta->getUserDataModel()->Name."Config::HOST, ".$this->meta->getUserDataModel()->Name."Config::LOGIN, ".$this->meta->getUserDataModel()->Name."Config::PASSWORD);
				mssql_select_db(\$database, \$link);
				return \$link;
			}
			public function getSafeName(\$name)
			{
			    if( \$name instanceof ORBDateTime )
			      \$name = \$name->getDateTime();

				return \"[\".\$name.\"]\";
			}

			public /*String*/ function getTableName()
			{
				return \"" . $this->tableName . "\";
			}

			public function getRelation( \$tableName )
			{
				return NULL;
			}

			public function getSafeParam(\$param, \$isNullable, \$link)
			{
				return \"'\".str_replace(\"'\",\"''\",\$param).\"'\";
			}

			public function getCommandBuilder()
      		{
         		return new MsSqlCommandBuilder();
      		}

      		public /*int*/ function getRowCountBySql( /*String*/ \$sqlQuery )
			{
	    		/*int*/ \$rowCount = 0;

	    		/*int*/ \$selectIndex = stripos( strtoupper( \$sqlQuery ), \"SELECT\" ) + 6;
	    		/*int*/ \$fromIndex = stripos( strtoupper( \$sqlQuery ), \" FROM \" );

	    		// check for top keyword

	    		if( stripos( strtoupper( \$sqlQuery ), \" TOP \" ) != false )
	    		{
	        		/*int*/ \$topIndex = stripos( strtoupper( \$sqlQuery ), \" TOP \" ) + 4;
	        		/*bool*/ \$digitFound = false;

	        		foreach( str_split( substr( \$sqlQuery, \$topIndex ) ) as /*char*/ \$symbol)
	        		{
	            		\$topIndex += 1;

			            if( is_int( \$symbol ) )
	    		            \$digitFound = true;
	            		elseif ( \$digitFound )
	                		break;
	       		 }

			        \$selectIndex = \$topIndex;
	   		 	}

	   			/*String*/ \$selectPart = substr( \$sqlQuery, \$selectIndex, \$fromIndex - \$selectIndex );
	    		/*String*/ \$modifiedQuery = str_replace( \$selectPart, \" count(*) \", \$sqlQuery );
	    		/*int*/ \$orderByIndex = stripos( strtoupper( \$modifiedQuery ), \"ORDER BY\" );

	    		if( \$orderByIndex != false )
	        		\$modifiedQuery = substr( \$modifiedQuery, 0, \$orderByIndex );

	    		\$connection = \$this->getConnection();
				\$result = mssql_query( \$modifiedQuery, \$connection );

	    		\$row = mssql_fetch_row( \$result );

	    		return \$row[ 0 ];
			}
		");

		$this->generatePart(new WDMServerPHPDataMsSQLFindAllCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLCreateCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLFindByPrimaryKeyCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLLoadCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLRemoveCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLSaveCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMsSQLUpdateCodegenerator($this->tableName));
		$this->writeText("
		}
		?>");
	}
}
?>