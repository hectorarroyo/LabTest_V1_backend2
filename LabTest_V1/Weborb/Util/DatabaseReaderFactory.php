<?php
/*******************************************************************
 * DatabaseReaderFactory.php
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
 
 require_once(WebOrb . "Util/DatabaseReaders/MsSqlDatabaseReader.php");
 require_once(WebOrb . "Util/DatabaseReaders/MySqlDatabaseReader.php");
   
 class DatabaseReaderFactory
 {
    private $m_readers;
   
    private function __construct()
    {
        $this->m_readers = array();
        
        $this->m_readers['mssql'] = new MsSqlDatabaseReader();
        $this->m_readers['mysql'] = new MySqlDatabaseReader();        
    }
   
   
    private static $s_instance = null;
    
    private static function getInstance()
    {
        if(self::$s_instance == null)
            self::$s_instance = new DatabaseReaderFactory();
        
        return self::$s_instance;
    }
     
    public static function getReader($databaseName)  
    {
        if(array_key_exists($databaseName,self::getInstance()->m_readers))
            return self::getInstance()->m_readers[$databaseName];
            
        return null;
    }
 }
 
?>