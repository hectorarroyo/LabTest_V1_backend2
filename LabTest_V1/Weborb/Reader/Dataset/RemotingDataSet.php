<?php
/*******************************************************************
 * RemotingDataSet.php
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


require_once(WebOrb . "Reader/Dataset/DataSetInfo.php");

class RemotingDataSet
{
    private $m_dataArr;
    
    public function __construct($dataArr)
    {
        $this->m_dataArr = $dataArr;
    }
  
    public function getDatasetInfo()  
    {
        $rowCount = count($this->m_dataArr);
      
        $dataSetInfo = new DataSetInfo($this);
        
        $dataSetInfo->setCurrentRowIndex(1);
        $dataSetInfo->setNumberOfRows($rowCount);
        $dataSetInfo->setPagingSize($rowCount);
        
        if($rowCount > 0)
        {
          $values = array();
          
          for($i = 0; $i < count($this->m_dataArr); $i++)
            $values[] = array_values((array)$this->m_dataArr[$i]);
          
          $dataSetInfo->setRecordsData($values);  

          $columnNames = array_keys((array)$this->m_dataArr[0]);
                    
          $dataSetInfo->setColumnNames($columnNames); 
        }
        else
        {
           $emptyArr = array();
           $dataSetInfo->setColumnNames($emptyArr); 
           $dataSetInfo->setRecordsData($emptyArr);         
        }
        
        return $dataSetInfo;
    }
}

?>