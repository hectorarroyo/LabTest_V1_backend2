<?php
/*******************************************************************
 * MSSQLInspectorFactory.php
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
require_once("MSSQLInspector2005.php");
require_once("MSSQLInspector.php");
class MSSQLInspectorFactory
{
    public static function getInspector($hostname, $userid, $password)
    {
    	   
    	$connection = mssql_connect($hostname, $userid, $password);

        if ($connection != false)
        {
	        $result = mssql_query("select @@version");
	        $row = mssql_fetch_row($result);

	        if (stripos($row[0], "Microsoft SQL Server 2005") !== false)
				return new MSSQLInspector2005($hostname, $userid, $password);
			else
				return new MSSQLInspector($hostname, $userid, $password);
	        
			mssql_close($connection);        	
        }
        else
        	throw new Exception("Unable to connect to server");
    }
}
?>