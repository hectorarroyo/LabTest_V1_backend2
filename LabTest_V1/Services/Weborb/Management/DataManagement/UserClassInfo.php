<?php
/*******************************************************************
 * UserClassInfo .php
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
require_once(WebOrbServicesPath . "Weborb/Data/Inspector/RelationType.php");
class UserClassInfo 
{
    public $Name;
    public $TableName;
    public $DatabaseConnectionInfoId;
    public $Fields = array();
    public $Relations = array();

    public function GetField($columnName)
    {
    	foreach ($this->Fields as $field)
    	{
    		if ($field->ColumnName == $columnName)
    			return $field;
    	}
    	
    	return null;
    }

    public function GetRelation($foreignKey, $type)
    {
    	foreach ($this->Relations as $relation)
    	{
    		if ($relation->ForeignKey == $foreignKey && $relation->Type == $type)
    			return $relation;
    	}
    	
    	return null;
    }

}
?>