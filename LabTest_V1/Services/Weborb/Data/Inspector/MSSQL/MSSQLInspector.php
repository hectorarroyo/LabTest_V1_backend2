<?php
/*******************************************************************
 * MSSQLInspector.php
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

require_once(WebOrbServicesPath . "Weborb/Data/Inspector/IInspector.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/StoredProcedureInfo.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/DataTypeInfo.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/ParameterInfo.php");
class MSSQLInspector implements IInspector
{
	protected $hostname;
	protected $userid;
	protected $password;
	protected $connection;

	public function __construct($hostname, $userid, $password)
	{
	    $this->hostname = $hostname;
	    $this->userid = $userid;
	    $this->password = $password;
	    $this->connection = mssql_connect($hostname, $userid, $password, false);
	}

	public function GetDatabases()
	{
		$collection = array();

        $result = mssql_query("SELECT CATALOG_NAME FROM INFORMATION_SCHEMA.SCHEMATA");

		while ($row = mssql_fetch_row($result))
		{
			$collection[] = $row[0];
		}

		return $collection;
	}

    public function GetTables($database)
    {
		$collection = array();

		mssql_select_db($database);

        $result = mssql_query("	SELECT TABLE_NAME , TABLE_SCHEMA
                        		FROM INFORMATION_SCHEMA.TABLES
                        		Where TABLE_TYPE = 'BASE TABLE' and TABLE_NAME != 'dtproperties' and TABLE_NAME != 'sysdiagrams'");

		while ($row = mssql_fetch_row($result))
		{
			$collection[] = new TableInfo($row[0], $row[1]);
		}

		return $collection;
    }

    public function GetColumns($database, $schema, $table)
    {
        $columns = array();
        $query = "SELECT
                c.COLUMN_NAME,
                c.IS_NULLABLE,
                c.DATA_TYPE,
                (SELECT tc.CONSTRAINT_TYPE FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE ccu,
			        INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
		                WHERE   c.TABLE_NAME = ccu.TABLE_NAME
                        and c.COLUMN_NAME = ccu.COLUMN_NAME
                        and tc.CONSTRAINT_TYPE='PRIMARY KEY' and tc.TABLE_NAME = c.TABLE_NAME
                        and tc.CONSTRAINT_NAME = ccu.CONSTRAINT_NAME),
                COLUMNPROPERTY(OBJECT_ID(c.TABLE_NAME), c.COLUMN_NAME, 'IsIdentity')
                FROM INFORMATION_SCHEMA.COLUMNS c
             WHERE c.TABLE_NAME='".$table."'";

        mssql_select_db($database);
        $result = mssql_query($query);

	   	while ($row = mssql_fetch_row($result))
		{
			$column = new ColumnInfo();
			$column->name = $row[0];
			$column->isNullable = strtolower($row[1]) == "yes" ? true : false;
			$column->dataTypeInfo = new ColumnDataTypeInfo($row[2]);
			$column->keyType = $row[3] == "" ? ColumnKeyType::NONE : $this->ProcessKeyType($row[3]);
			$column->isAutoIncrement = $row[4] == 1;

			$columns[] = $column;
		}

		return $columns;
    }

	public function getRelations($database, $table, $relationType)
	{
	    $relations = array();

	    mssql_select_db($database);

		if ($relationType == RelationType::Parent)
		{
			$query = "SELECT t1.CONSTRAINT_NAME, t1.COLUMN_NAME, t1.ORDINAL_POSITION
			      FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE t1, INFORMATION_SCHEMA.TABLE_CONSTRAINTS t2
			      Where t2.TABLE_CATALOG = t1.TABLE_CATALOG
			      AND t2.TABLE_SCHEMA = t1.TABLE_SCHEMA
			      AND t2.TABLE_NAME = t1.TABLE_NAME
			      AND t2.CONSTRAINT_NAME = t1.CONSTRAINT_NAME
			      and t1.TABLE_Catalog = '".$database."' AND t1.TABLE_NAME = '".$table."'
			      AND CONSTRAINT_TYPE = 'FOREIGN KEY'";

			$mappedRelations = array();

			$result = mssql_query($query);

			while ($row = mssql_fetch_row($result))
			{
				$fkName = $row[0];

				if (!array_key_exists($fkName, $mappedRelations))
				    $mappedRelations[$fkName] = new RelationInfo($table, $fkName, $relationType);

				$mappedRelations[$fkName]->Columns[] = new RelationColumnInfo($row[1], "");
			}

			foreach ($mappedRelations as $relation)
			{
				$query = "select t1.CONSTRAINT_NAME, t1.COLUMN_NAME, t1.ORDINAL_POSITION, t1.TABLE_NAME
					      from INFORMATION_SCHEMA.KEY_COLUMN_USAGE t1, INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS t2
					      where t2.CONSTRAINT_NAME = '".$relation->ForeignKey."'
					      and t2.UNIQUE_CONSTRAINT_NAME = t1.CONSTRAINT_NAME
					      and TABLE_CATALOG = '".$database."'
					      order by ORDINAL_POSITION";

		      	$result = mssql_query($query);
				while ($row = mssql_fetch_row($result))
				{
					$relation->Columns[$row[2] - 1]->RelatedColumnName = $row[1];
					$relation->RelatedTableName = $row[3];
				}

				$relations[] = $relation;
			}
		}
		else
		{
		    $dependentTables = array();

			$query = "SELECT  sysobjects.name
			            FROM    sysobjects
			            INNER JOIN  syscolumns ON sysobjects.id = syscolumns.id
			            INNER JOIN  sysforeignkeys ON syscolumns.id = sysforeignkeys.fkeyid
			               AND  syscolumns.colid = sysforeignkeys.fkey
			            INNER JOIN  syscolumns syscolumns2
			               ON sysforeignkeys.rkeyid = syscolumns2.id
			               AND  sysforeignkeys.rkey = syscolumns2.colid
			            INNER JOIN  sysobjects sysobjects2 ON syscolumns2.id = sysobjects2.id
			            WHERE   sysobjects2.name = '".$table ."'
			            group by
			            sysobjects.name ";

	      	$result = mssql_query($query);

			while ($row = mssql_fetch_row($result))
			{
				$dependentTables[] = $row[0];
			}

		    foreach ($dependentTables as $childTable)
		    {
		        foreach ($this->getRelations($database, $childTable, RelationType::Parent) as $relationInfo)
		        {
		            if ($relationInfo->RelatedTableName == $table)
		            {
		                $childRelation = new RelationInfo($table, $relationInfo->ForeignKey, RelationType::Child);

		                $childRelation->RelatedTableName = $childTable;

		                foreach ($relationInfo->Columns as $relationColumnInfo)
		                {
		                    $childRelation->Columns[] = new RelationColumnInfo($relationColumnInfo->RelatedColumnName, $relationColumnInfo->ColumnName);
		                }

		                $relations[] = $childRelation;
		            }
		        }
		    }
		}

	    return $relations;
	}

	protected function ProcessKeyType($keyTypeStr)
	{
	    $keyTypeStr = strtoupper($keyTypeStr);

	    if($keyTypeStr == "PRIMARY KEY")
	        return ColumnKeyType::PRIMARY;
	    else
	        return ColumnKeyType::NONE;
	}

	public function GetData($database, TableInfo $tableInfo, $count)
	{
		mssql_select_db($database);
		$query = "Select Top $count * From [$tableInfo->SchemaName].[$tableInfo->Name]";
		$result = mssql_query($query);

		return $result;
	}

    public function IsTableExists($database, $tableInfo)
    {
        return true;
    }

	public /*array*/ function getStoredProcedures(/*string*/ $database)
	{
	    $storedProcedureList = array();

		mssql_select_db($database);

		$result = mssql_query( "SELECT SPECIFIC_NAME
								FROM INFORMATION_SCHEMA.ROUTINES
                				WHERE ROUTINE_TYPE = 'PROCEDURE' ORDER BY SPECIFIC_NAME");

	    while ($row = mssql_fetch_row($result))
		{
			if (substr($row[0], 0, 3) != "dt_")
				$storedProcedureList[] = new StoredProcedureInfo($row[0]);
		}

	    foreach ($storedProcedureList as /*StoredProcedureInfo*/ $storedProcedure)
        {
			$result = mssql_query( "SELECT PARAMETER_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
       							    FROM INFORMATION_SCHEMA.PARAMETERS
            						WHERE SPECIFIC_NAME = '".$storedProcedure->Name."' ORDER BY ORDINAL_POSITION");

			while ($row = mssql_fetch_row($result))
			{
				/*DataTypeInfo*/ $dataType = null;

				if ($row[2] == "")
				    $dataType = new DataTypeInfo($row[1]);
				else
				    $dataType = new DataTypeInfo($row[1], $row[2]);

				$storedProcedure->Parameters[] = new ParameterInfo(substr($row[0], 1), $dataType);
			}
        }

	    return $storedProcedureList;
	}
}
?>