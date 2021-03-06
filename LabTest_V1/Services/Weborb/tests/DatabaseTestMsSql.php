﻿<?php
/*******************************************************************
 * DatabaseTestMsSql.php
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

    class DatabaseTestMsSql
    {
      public function getCustomers($rowsToGet)
      {
          if($rowsToGet > -1)
            return $this->executeSql("select top $rowsToGet * from customers");
          
          return $this->getCustomersTable();
      }
    
      public function getCustomersMultiTable()
      {
         $resultSet = $this->execute("select top 10 * from customers");
         
         $resultArr = array();
         
         while($row = mssql_fetch_object($resultArr))
            $resultArr[] = $row;
         
         return array($resultArr,$resultArr);
         
      }
    
      public function executeSql($sql)
      {

		$server="MSSQLSERVER";
		$username="sa";
		$password="go";

		$sqlconnect=mssql_connect($server, $username, $password);

		mssql_select_db('Northwind');
		
		return mssql_query($sql);       
     }
     
     public function getCustomersTable()
     {
          return $this->executeSql("select top 10 * from customers");          
     }
     
    }
?>