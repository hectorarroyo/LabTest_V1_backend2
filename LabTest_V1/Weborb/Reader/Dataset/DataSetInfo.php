<?php
/*******************************************************************
 * DataSetInfo.php
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


 require_once(WebOrb . "V3Types/AckMessage.php"); // Need to move into util
 
 class DataSetInfo
 {
    public function __construct()
    {
         $this->serverInfo["version"] = 1.0;
         $this->serverInfo["serviceName"] = "Weborb.Reader.Dataset.RemotingDataSet";
         $this->serverInfo["id"] = AckMessage::uuid();// Need to move into util
    }
   
    public $serverInfo = array();
   
    public function setNumberOfRows($numberOfRows)
    {
      $this->serverInfo["totalCount"] = $numberOfRows;      
    }

    public function setCurrentRowIndex($currentRowIndex)
    {
      $this->serverInfo["cursor"] = $currentRowIndex;      
    }
        
    public function setColumnNames(&$columnNames)
    {
      $this->serverInfo["columnNames"] = $columnNames;      
    }
    
    public function setRecordsData(&$records)
    {
      $this->serverInfo["initialData"] = $records;
    }
    
    public function setPagingSize($size )
    {
      $this->serverInfo["pagingSize"] = $size;
    }

 }

?>