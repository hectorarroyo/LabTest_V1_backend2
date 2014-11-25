<?php
/*******************************************************************
 * DatabaseInfo.php
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
require_once(WebOrb. "V3Types/AckMessage.php");
require_once("DatabaseInfoType.php");
require_once("MySQL/MySQLInspector.php");
require_once("MSSQL/MSSQLInspectorFactory.php");
class DatabaseInfo
{
    public $type;
    public $hostname;
    public $port;
    public $username;
    public $id;
    public $password;

    private $inspector;

    public function __construct(/*DatabaseInfoType*/ $type, $hostname, $port, $username, $password)
    {
        $this->type = $type;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->id = AckMessage::uuid();
    }

    public function GetInspector()
    {
        if($this->inspector == null)
            $this->inspector = self::CreateInspector($this);

        return $this->inspector;
    }

    private static function CreateInspector(DatabaseInfo $dbInfo)
    {
        switch($dbInfo->type)
        {
            case DatabaseInfoType::MYSQL:
                return new MySQLInspector($dbInfo->hostname, $dbInfo->port, $dbInfo->username, $dbInfo->password);
            case DatabaseInfoType::MSSQL:
                return MSSQLInspectorFactory::getInspector($dbInfo->hostname, $dbInfo->username, $dbInfo->password);
        }

        throw new Exception( "unknown database type" );
    }
}
?>