<?php
/*******************************************************************
 * V3Dispatcher.php
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

    require_once(WebOrb . "Dispatch/IDispatch.php");
    require_once(WebOrb . "ORBConstants.php");
    require_once(WebOrb . "Reader/NamedObject.php");
    require_once(WebOrb . "V3Types/V3Message.php");
    require_once(WebOrb . "Util/Logging/LoggingConstants.php");

    class V3Dispatcher
        implements IDispatch
    {

      public function dispatch(Request &$request)
      {
        if(!$this->isV3Request($request))
          return false;

        $authError = false;

        for($i = 0; $i < $request->getBodyCount(); $i++)
        {
          $request->setCurrentBody($i);
          $array = $request->getRequestBodyData();
          $class = new ReflectionClass("V3Message");


		  if( $array[ 0 ] instanceof NamedObject && $array[ 0 ]->canAdaptTo( $class ) )
	      {
	        /*NamedObject*/ $namedObject = $array[ 0 ];
	        /*V3Message*/ $v3message = $namedObject->defaultAdapt();
	        $v3message = $v3message->execute( $request );
	        $request->setResponseBodyPart( $v3message );

	        if( $v3message->IsError() )
	          $request->setResponseURI( ORBConstants::ONSTATUS );
	        else
	          $request->setResponseURI( ORBConstants::ONRESULT );
	      }
	      else
	      {
	      	Log::log(LoggingConstants::DEBUG, "cannot be adapted to V3Message");
	       	return false;
	      }
	    }

        return true;

      }

      private function isV3Request(Request $request)
      {
        if(strpos($request->getRequestURI(),"."))
            return false;

        if(!is_array($request->getRequestBodyData()))
            return false;

         return true;
      }


      public function getName()
      {
        return "V3 Dispatcher";
      }

    }

?>