<?php
/*******************************************************************
 * WebORBAuthenticationHandler.php
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

 require_once(WebOrb . "Security/IAuthenticationHandler.php");
 require_once(WebOrb . "Util/ThreadContext.php");
 require_once(WebOrb . "Exceptions/WebORBAuthenticationException.php"); 
 require_once(WebOrb . "Util/Logging/Log.php");
 require_once(WebOrb . "Util/Logging/LoggingConstants.php");
  
 class WebORBAuthenticationHandler
    implements IAuthenticationHandler
 {
      /*void*/ function checkCredentials( /*string*/ $userid, /*string*/ $password, Request $message )
      {
          $security = ThreadContext::getORBConfig()->getSecurity();
          
          if(!$security->authenticate($userid,$password))
          {
          	Log::log(LoggingConstants::EXCEPTION, "invalid userid or password u: " . $userid . ", p: " . $password . ".");
            throw new WebORBAuthenticationException( "invalid userid or password" );
          }
      }
 }

?>