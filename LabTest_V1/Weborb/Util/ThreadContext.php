<?php
/*******************************************************************
 * ThreadContext.php
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
 
 require_once(WebOrb . "Security/Credentials.php");    
 require_once(WebOrb . "Config/ORBConfig.php");  
 
 class ThreadContext
 {
   private static $s_instance = null;
   public static $properties;
   
   private function __construct()
   {
      //session_start();
   }
   public static function getInstance()
   {
     if(self::$s_instance == null)   
     {
        self::$s_instance = new ThreadContext();
     }
     
     return self::$s_instance;
   }
   
   public static function currentCallerCredentials()
   { 
     return self::getInstance()->getCredentials();
   }
   
   private function setCredentials($currentCallerCredentials)
   {
      $_SESSION["credentials"] = $currentCallerCredentials;
   }
   
   private function getCredentials()
   {
      return $_SESSION["credentials"];
   }   
   
   public static function setCallerCredentials($currentCallerCredentials)
   {
      self::getInstance()->setCredentials($currentCallerCredentials);
   }
   
   public static function getORBConfig()
   {
     return ORBConfig::getInstance();
   }
   public static /*Hashtable*/function getProperties()
   {
      if( self::$properties == null )
          self::$properties = array();

      return self::$properties;
   }
 }

?>