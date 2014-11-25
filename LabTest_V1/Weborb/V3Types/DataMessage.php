<?php
/*******************************************************************
 * DataMessage.php
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


    require_once(WebOrb . "V3Types/V3Message.php");
    require_once(WebOrb . "V3Types/AckMessage.php");
    
    class DataMessage
        extends V3Message
    {
      public $operation;
      
      public function setOperation($operation)
      {
        $this->operation = $operation;
      }
      
      public function getOperation()
      {
        return $this->operation;
      }
      
      public function execute(Request $request)
      {
        
            switch($this->operation)
            {
					case 1: // fill					
						return $this->handleFillRequest();

					case 3: // update
						return $this->handleUpdate();

					case 4: // delete
						return $this->handleDelete();

					case 7: // transaction
						return $this->handleTransaction( $request );

					case 8: // page
						return $this->handlePagingRequest();

					case 11: // create and sequence
						return $this->handleCreateAndSequence();

                    case 18: // collection release
                        return new AckMessage( $this->messageId, $this->clientId, null );
            }
          
      }

      private function handleFillRequest()
      {
        $pageSize = -1;
        $sequenceId = -1;
        $totalRecords = 0;
        $adaptedArgs = null;
        $dataList = array();
        
        if(array_key_exists("pageSize",$this->headers))
            $pageSize = (int)$this->headers["pageSize"];
            
        
        return null;
      }
      
      private function handleUpdate()
      {
        Log::log(LoggingConstants::DEBUG, "Called");
        
        return null;
      }
      
      private function handleDelete()
      {
        Log::log(LoggingConstants::DEBUG, "Called");
        
        return null;
      }
      
      private function handleTransaction()
      {
        Log::log(LoggingConstants::DEBUG, "Called");
        
        return null;
      }
      
      private function handlePagingRequest()
      {
        Log::log(LoggingConstants::DEBUG, "Called");
        
        return null;
      }
      
      private function handleCreateAndSequence()
      {
        Log::log(LoggingConstants::DEBUG, "Called");
        
        return null;
      }
    
      
    }

?>