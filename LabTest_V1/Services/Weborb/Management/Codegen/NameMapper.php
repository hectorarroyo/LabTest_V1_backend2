<?php
/*******************************************************************
 * NameMapper.php
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
class NameMapper extends DefaultNameMapper
{
    private /*UserDataModel*/ $m_model;
    private $m_dbNameToConnection = array();

    public function __construct(/*UserSettings*/ $settings, /*UserDataModel*/ $model)
    {
        $this->m_model = $model;

        foreach ($settings->DatabaseConnections as /*DatabaseConnectionInfo*/ $connection)
            $this->m_dbNameToConnection[$connection->DatabaseName] = $connection->Id;
    }

    public /*string*/ function getClassName(/*string*/ $tableName)
    {
        return parent::getClassName($tableName);
    }

    public /*string*/ function getPropertyName(/*string*/ $databaseName, /*string*/ $tableName, /*string*/ $columnName)
    {
        /*UserClassInfo*/ $userClassInfo = $this->m_model->GetClassInfo($this->m_dbNameToConnection[$databaseName], $tableName);

        if ($userClassInfo != null)
        {
            /*UserClassFieldInfo*/ $fieldInfo = $userClassInfo->GetField($columnName);

            if($fieldInfo != null)
                return $fieldInfo->Name;
        }

        return parent::getPropertyName($databaseName, $tableName, $columnName);
    }

    public /*string*/ function getChildProperty(/*string*/ $databaseName, /*string*/ $tableName, /*string*/ $childTable, /*string*/ $key)
    {
        /*UserClassInfo*/ $userClassInfo = $this->m_model->GetClassInfo($this->m_dbNameToConnection[$databaseName], $tableName);

        if ($userClassInfo != null)
        {
            /*UserRelationInfo*/ $relationInfo = $userClassInfo->GetRelation($key, RelationType::Child);

            if ($relationInfo != null)
                return $relationInfo->Alias;
        }

        return parent::getChildProperty($databaseName, $tableName, $childTable, $key);
    }

    public /*string*/ function getParentProperty(/*string*/ $database, /*string*/ $tableName, /*string*/ $parentTable, /*string*/ $key)
    {
        /*UserClassInfo*/ $userClassInfo = $this->m_model->GetClassInfo($this->m_dbNameToConnection[$database], $tableName);

        if ($userClassInfo != null)
        {
            /*UserRelationInfo*/ $relationInfo = $userClassInfo->GetRelation($key, RelationType::Parent);

            if ($relationInfo != null)
                return $relationInfo->Alias;
        }

        return parent::getParentProperty($database, $tableName, $parentTable, $key);
    }
}

?>