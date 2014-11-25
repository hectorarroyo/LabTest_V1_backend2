<?php
/*******************************************************************
 * MySQLInspector.php
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
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/TableInfo.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/ColumnInfo.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/ColumnKeyType.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/ForeignKeyData.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/RelationInfo.php");
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/RelationColumnInfo.php");

class MySQLInspector implements IInspector
{
    private $hostname;
    private $userid;
    private $password;
    private $port = "3306";
    private $connection;

    public function __construct($hostname, $port, $userid, $password)
    {
        $this->hostname = $hostname;
        if($port == "") $port = 3306;
        $this->port = $port;
        $this->userid = $userid;
        $this->password = $password;
        $this->connection = mysql_connect($hostname.":".$port, $userid, $password);
        if ($this->connection == false)
        	throw new Exception(mysql_error());
    }

    public function GetDatabases()
    {
		$collection = array();

		//mysql_select_db("information_schema", $this->connection);

        $result = mysql_query("SHOW DATABASES");

		while ($row = mysql_fetch_row($result))
		{
			$collection[] = $row[0];
		}

		return $collection;
    }

    public function GetTables($database)
    {
		$collection = array();

		mysql_select_db($database, $this->connection);

        $result = mysql_query("SHOW TABLES ");

		while ($row = mysql_fetch_row($result))
		{
			$collection[] = new TableInfo($row[0], $database);
		}

		return $collection;
    }

    public function getRelations($database, $table, $relationType)
    {
		$parent_relation = "
		    select
		        constraint_name,
		        referenced_table_name as related_table_name,
		        referenced_column_name,
		        column_name
		    from key_column_usage
		    where table_schema = '".$database."' and table_name = '".$table."' and referenced_table_schema is not null;";

	    $child_relation = "
	        select
	            constraint_name,
	            table_name as related_table_name,
	            referenced_column_name,
	            column_name
	        from key_column_usage
	        where table_schema = '".$database."' and referenced_table_name = '".$table."' and referenced_table_schema is not null;";

        $relations = array();

    	mysql_select_db("information_schema", $this->connection);

    	$query = $relationType == RelationType::Parent ? $parent_relation : $child_relation;

        $result = mysql_query($query);

        $fkMap = array();

        while ($row = mysql_fetch_row($result))
		{
			$fk = $row[0];

			if (!array_key_exists($fk, $fkMap))
			{
			    $fkMap[$row[0]] = new RelationInfo($table, $fk, $relationType);

			    $fkMap[$fk]->RelatedTableName = $row[1];

			    $relations[] = $fkMap[$fk];
			}

			$fkMap[$fk]->Columns[] = new RelationColumnInfo($row[3], $row[2]);
		}

        return $relations;
    }

    private function ProcessKeyType($keyTypeStr)
    {
        $keyTypeStr = strtolower($keyTypeStr);

        if($keyTypeStr == "pri")
            return ColumnKeyType::PRIMARY;
        else if($keyTypeStr == "uni")
            return ColumnKeyType::UNIQUE;
        else if($keyTypeStr == "mul")
            return ColumnKeyType::FULLTEXT;
        else
            return ColumnKeyType::NONE;
    }

    public function GetColumns($database, $schema, $table)
    {
        $columns = array();
        $query = "select column_name, is_nullable, data_type, column_key, extra from columns where table_schema = '".$database."' and table_name = '".$table."';";
        mysql_select_db("information_schema", $this->connection);
        $result = mysql_query($query);

    	while ($row = mysql_fetch_row($result))
		{
			$column = new ColumnInfo();

			$column->name = $row[0];
			$column->isNullable = strtolower($row[1]) == "yes" ? true : false;
			$column->dataTypeInfo = new ColumnDataTypeInfo($row[2]);
			$column->keyType = $row[3] == "" ? ColumnKeyType::NONE : $this->ProcessKeyType($row[3]);
			$column->isAutoIncrement = $row[4] == "" ? false : strtolower($row[4]) == "auto_increment" ? true : false;
			$column->foreignKey = $this->GetForeignKey($database, $table, $column->name);

			$columns[] = $column;
		}

        return $columns;
    }

    private function GetForeignKey($database, $table, $column)
    {
        $query = "
            select referenced_table_schema, referenced_table_name,
                    referenced_column_name
            from key_column_usage
            where table_schema = '".$database."' and table_name = '".$table."' and column_name = '".$column."' and referenced_table_schema is not null;";

        mysql_select_db("information_schema", $this->connection);
        $result = mysql_query($query);

        while ($row = mysql_fetch_row($result))
		{
            $foreignKeyData = new ForeignKeyData();
            $foreignKeyData->database = $row[0];
            $foreignKeyData->table = $row[1];
            $foreignKeyData->column = $row[2];
            return $foreignKeyData;
		}

		return null;
    }

	public function GetData($database, TableInfo $tableInfo, $count)
	{
		mysql_select_db($tableInfo->SchemaName, $this->connection);

        $result = mysql_query("SELECT * FROM ".$tableInfo->Name." LIMIT 0, $count;");

	    return $result;
	}

    public function IsTableExists($database, $tableInfo)
    {
        return true;
    }

	public /*array*/ function getStoredProcedures(/*string*/ $database)
	{
	    $storedProcedureList = array();

		mysql_select_db($database, $this->connection);

		$result = mysql_query( "SELECT SPECIFIC_NAME
								FROM INFORMATION_SCHEMA.ROUTINES
            					WHERE ROUTINE_TYPE = 'PROCEDURE' ORDER BY SPECIFIC_NAME");

	    while ($row = mysql_fetch_row($result))
		{
			$storedProcedureList[] = new StoredProcedureInfo($row[0]);
		}

        foreach ($storedProcedureList as /*StoredProcedureInfo*/ $storedProcedure)
        {
			$result = mysql_query( "SELECT PARAMETER_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
       							    FROM INFORMATION_SCHEMA.PARAMETERS
            						WHERE SPECIFIC_NAME = '".$storedProcedure->Name."' ORDER BY ORDINAL_POSITION");

			while ($row = mysql_fetch_row($result))
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