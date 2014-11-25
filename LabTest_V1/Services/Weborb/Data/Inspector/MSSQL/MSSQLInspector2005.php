<?php
/*******************************************************************
 * MSSQLInspector2005.php
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

require_once("MSSQLInspector.php");
class MSSQLInspector2005 extends MSSQLInspector
{
    public function GetColumns($database, $schema, $table)
    {
        $columns = array();
        $query = "SELECT
            c.COLUMN_NAME,
            c.IS_NULLABLE,
            c.DATA_TYPE,
            (SELECT tc.CONSTRAINT_TYPE
               FROM INFORMATION_SCHEMA.CONSTRAINT_COLUMN_USAGE AS ccu INNER JOIN
                    INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS tc ON ccu.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
               WHERE (c.TABLE_NAME = ccu.TABLE_NAME) AND
                     (c.COLUMN_NAME = ccu.COLUMN_NAME) AND
                     (tc.CONSTRAINT_TYPE = 'PRIMARY KEY') AND
                     (tc.TABLE_NAME = c.TABLE_NAME)) AS EXPR1,
            COALESCE (sysc.is_identity, 1, 0) AS EXPR2
        FROM INFORMATION_SCHEMA.COLUMNS AS c INNER JOIN
             sys.columns AS sysc ON c.TABLE_NAME = OBJECT_NAME(sysc.object_id) AND c.COLUMN_NAME = sysc.name
        WHERE (c.TABLE_NAME = '".$table."')";

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

    public function GetDatabases()
    {
		$collection = array();

        $result = mssql_query("select name from sys.databases");

		while ($row = mssql_fetch_row($result))
		{
			$collection[] = $row[0];
		}

		return $collection;
    }
}
?>