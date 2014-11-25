<?php
/*******************************************************************
 * TableMeta.php
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
class TableMeta
{
    public /*ColumnInfo*/ $Columns = array();
    public /*RelationInfo*/ $Relations = array();

    public /*array*/ function getPrimaryKeys()
    {
    	$columns = array();
    	foreach ($this->Columns as $column)
    	{
    		if ($column->keyType == ColumnKeyType::PRIMARY)
    			$columns[] = $column; 
    	}
    	
    	return $columns;
    }
    
    public function IsDepends($table, $meta, $proceedList)
    {
        $proceedList[] = $this;

        foreach ($this->Relations as $relationInfo)
        {
            if ($relationInfo->Type == RelationType::Parent)
            {
                if($relationInfo->RelatedTableName == $table)
                    return true;

                if (in_array($meta[$relationInfo->RelatedTableName],$proceedList))
                    continue;

                if ($meta[$relationInfo->RelatedTableName]->IsDepends($table, $meta, $proceedList))
                    return true;
            }
        }

        return false;
    }
}
?>