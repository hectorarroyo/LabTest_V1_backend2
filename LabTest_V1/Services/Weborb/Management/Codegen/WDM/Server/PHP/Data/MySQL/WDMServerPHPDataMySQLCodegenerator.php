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
require_once("WDMServerPHPDataMySQLCreateCodegenerator.php");
require_once("WDMServerPHPDataMySQLFindByPrimaryKeyCodegenerator.php");
require_once("WDMServerPHPDataMySQLLoadCodegenerator.php");
require_once("WDMServerPHPDataMySQLRemoveCodegenerator.php");
require_once("WDMServerPHPDataMySQLSaveCodegenerator.php");
require_once("WDMServerPHPDataMySQLUpdateCodegenerator.php");
require_once("WDMServerPHPDataMySQLFindAllCodegenerator.php");

class WDMServerPHPDataMySQLCodegenerator extends WDMCodegenerator
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
  		require_once(WebOrbServicesPath. \"Weborb/Data/Management/MySqlCommandBuilder.php\");
  		require_once(WebOrbServicesPath. \"Weborb/Data/Management/DomainObject.php\");
  		require_once(WebOrbServicesPath. \"" . str_replace('.', '/', $this->meta->getServerNamespace()) . "/" . $this->meta->getUserDataModel()->Name . "Config.php\");
		require_once(WebOrbServicesPath. \"" . str_replace('.', '/', $this->meta->getServerNamespace()) . "/" . $this->className . ".php\");
		require_once(WebOrb.\"Util/ORBDateTime.php\");

		class ".$this->tablePrefix.$this->className."DataMapper extends DataMapper
		{
			public function getConnection()
			{
				\$database = ".$this->meta->getUserDataModel()->Name."Config::DATABASE;
				\$link = mysql_connect( ".$this->meta->getUserDataModel()->Name."Config::HOST . \":\" . ".$this->meta->getUserDataModel()->Name."Config::PORT, ".$this->meta->getUserDataModel()->Name."Config::LOGIN, ".$this->meta->getUserDataModel()->Name."Config::PASSWORD);
				mysql_select_db(\$database, \$link);
				return \$link;
			}
			public function getSafeName(\$name)
			{
				return \"`\".\$name.\"`\";
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
			    if( \$param instanceof ORBDateTime )
			      \$param = \$param->getDateTime();

				if(get_magic_quotes_gpc())
				{
					\$param = stripslashes(\$param);
				}
				if (\$isNullable == \"1\" && \$param == \"\")
					return \"null\";
				else
					return \"'\".mysql_real_escape_string(\$param, \$link).\"'\";
			}
			public function getCommandBuilder()
      		{
         		return new MySqlCommandBuilder();
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
				\$result = mysql_query( \$modifiedQuery, \$connection );

	    		\$row = mysql_fetch_row( \$result );

	    		return \$row[ 0 ];
			}

		");

		$this->generatePart(new WDMServerPHPDataMySQLFindAllCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLCreateCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLFindByPrimaryKeyCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLLoadCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLRemoveCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLSaveCodegenerator($this->tableName));
		$this->generatePart(new WDMServerPHPDataMySQLUpdateCodegenerator($this->tableName));
		$this->writeText("
		}
		?>");
	}
}
?>