<?php
/*******************************************************************
 * AckMessage.php
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
    require_once(WebOrb . "Message/Request.php");
    require_once(WebOrb . "V3Types/BodyHolder.php");

    class AckMessage
          extends V3Message
    {

      public function __construct($correlationId = null,$clientId = null, $obj=null, $headers = null)
      {
	        $this->correlationId = $correlationId;
	        $this->clientId = is_null($clientId) ? self::uuid() : $clientId;
	        $this->messageId = self::uuid();
	
	
	        $this->timestamp = mktime();
	        $this->body = new BodyHolder();
	        $this->body->setBody($obj);
	        $this->destination = "";
	
	        if($headers == null)
	        	$this->headers = array( "dummy" => "header" );
	        else
	        	$this->headers = $headers;
	
	        $this->timeToLive = 0;

      }

      public function execute(Request $request)
      {
        throw new Exception( "AckMessage should never be execution target" );
      }

    public static function uuid()
    {
        return sprintf(
            '%08X-%04X-%04X-%02X%02X-%012X',
            mt_rand(),
            mt_rand(0, 65535),
            bindec(substr_replace(
            sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
            ),
            bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
            mt_rand(0, 255),
            mt_rand()
        );
    }


    }

?>