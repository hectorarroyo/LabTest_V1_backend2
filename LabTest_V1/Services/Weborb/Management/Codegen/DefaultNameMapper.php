<?php
/*******************************************************************
 * DefaultNameMapper.php
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
require_once("INameMapper.php");
class DefaultNameMapper implements INameMapper
{
    public static function getSafeName($name)
    {
	    $className = str_replace(' ', '_', $name);
        $className = str_replace('-', '_', $className);
        $className = str_replace('.', '_', $className);

        $nameInChars = str_split($className);
        $nameInChars[0] = strtoupper($nameInChars[0]);
        return implode($nameInChars);
    }

    public function getClassName($tableName)
    {
        return self::getSafeName($tableName);
    }

    public function getPropertyName($databaseName, $tableName, $columnName)
    {
        return self::getSafeName($columnName);
    }

    public static function isNameSafe($name)
    {
    	$pos1 = strpos($name, '-');
    	$pos2 = strpos($name, ' ');
    	$pos3 = strpos($name, '.');

        return $pos1 === false && $pos2 === false && $pos3 === false;
    }

    public function getChildProperty($database, $table, $childTable, $key)
    {
        return "Related" . self::getSafeName($childTable);
    }

    public function getParentProperty($database, $table, $parentTable, $key)
    {
        return "Related" . self::getSafeName($parentTable);
    }
}
?>