<?php
/*******************************************************************
 * V3Message.php
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


    require_once(WebOrb . "Message/Request.php");

    abstract class V3Message
    {
      public $timeToLive;
      public $clientId;
      public $timestamp;
      public $messageId;
      public $headers;
      public $destination;
      public $correlationId;
      public $body;
      private $isError = false;

      public abstract function execute(Request $request);

      public function IsError()
      {
         return $this->isError;
      }

      public function SetError()
      {
      	 $this->isError = true;
      }

    }
?>