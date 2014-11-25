<?php
/*******************************************************************
 * ReqMessage.php
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
    require_once(WebOrb . "Util/ThreadContext.php");
    require_once(WebOrb . "V3Types/ErrMessage.php");
    require_once(WebOrb . "V3Types/CommandMessage.php");
    require_once(WebOrb . "Security/ORBSecurity.php");
    require_once(WebOrb . "Security/Credentials.php");
    require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
    require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

    class ReqMessage
        extends V3Message
    {
      public $operation;
      public $source;
      public $messageRefType;

      const AUTHENTICATION_MESSAGE_REF_TYPE = "flex.messaging.messages.AuthenticationMessage";

      const CLIENT_PING_OPERATION = 5;
      const CLIENT_SYNC_OPERATION = 4;
      const CLUSTER_REQUEST_OPERATION = 7;
      const LOGIN_OPERATION = 8;
      const LOGOUT_OPERATION = 9;
      const POLL_OPERATION = 2;
      const SESSION_INVALIDATE_OPERATION = 10;
      const SUBSCRIBE_OPERATION = 0;
      const UNKNOWN_OPERATION = 1000;
      const UNSUBSCRIBE_OPERATION = 1;


      public function setOperation($operation)
      {
      	$this->operation = $operation;
      }

      public function getOperation()
      {
        return $this->operation;
      }

	  public function setSource($source)
	  {
  	    $this->source = $source;
	  }

	  public function getSource()
	  {
	    return $this->source;
	  }

      public function setMessageRefType($value)
      {
        $this->messageRefType = $value;
      }

      public function getMessageRefType()
      {
        return $this->messageRefType;
      }

      public function isAuthenticateMessage()
      {
        return $this->body->getBody() == ReqMessage::AUTHENTICATION_MESSAGE_REF_TYPE;
      }

      public function execute(Request $request)
      {

        if ("5" == $this->operation || "2" == $this->operation || "0" == $this->operation || "1" == $this->operation)
        {

//        	$bodyData = $request->getRequestBodyData();
//          	$namedObject = $bodyData[0];
//            /*CommandMessage*/ $commandMessage = new CommandMessage($this->operation, $namedObject);
//          	return $commandMessage->execute($request);
        }
        else if( "9" == $this->operation )
        {
            ThreadContext::setCallerCredentials( null );

            return new AckMessage($this->messageId, $this->clientId, null);
        }
        else if("8" == $this->operation)
        {

            $arr = $this->body->getBody();
            $adaptingType = $arr[0];

            $authData = split(":", base64_decode($adaptingType->defaultAdapt()));

            $credentials = new Credentials($authData[0],$authData[1]);

            $authHandler = ORBSecurity::getAuthenticationHandler( ThreadContext::getORBConfig() );

            Log::log( LoggingConstants::DEBUG, "got auth handler " . get_class($authHandler) );

            Log::log(LoggingConstants::MYDEBUG, "file: 'ReqMessage.php' got auth handler " . get_class($authHandler) );

            if( $authHandler == null )
            {
                $errorMessage = new ErrMessage( $this->messageId, new ServiceException( "Missing authentication handler" ) );
                $errorMessage->faultCode = "Client.Authentication";
                return $errorMessage;
            }

            try
            {
                $authHandler->checkCredentials($credentials->getUserId(),
                    $credentials->getPassword(),
                    $request );

                Log::log( LoggingConstants::DEBUG, "credentials are valid ");

                ThreadContext::setCallerCredentials($credentials);
            }
            catch(Exception $e)
            {
            	Log::log( LoggingConstants::EXCEPTION, "authentication exception", $e );

                $errorMessage = new ErrMessage( $this->messageId, $e );
                $errorMessage->faultCode = "Client.Authentication";

                return $errorMessage;
            }

            return new AckMessage($this->messageId, $this->clientId, null);
        }
        else
        {
          if(is_null($this->body->getBody()))
          {
            $arr = array(0);
            $this->body->setBody($arr);
          }
          else if(!is_array($this->body->getBody()))
          {
            $arr = array($this->body->getBody());
            $this->body->setBody($arr);
          }

          try
          {
//          	Log::log(LoggingConstants::MYDEBUG, $_SESSION["credentials"]);
			$resolvedName = ServiceRegistry::getMapping( $this->destination );

			if( $resolvedName == "*" )
				$this->destination = $this->source;

			$body = $this->body->getBody();

            $returnValue = Invoker::handleInvoke(
                                                $request,
                                                $this->destination,
                                                $this->operation,
                                                $body);

            return new AckMessage( $this->messageId, $this->clientId, $returnValue );
          }
          catch( Exception $e )
          {
          	Log::log( LoggingConstants::EXCEPTION, "method invocation exception" . $e );

          	return new ErrMessage( $this->messageId, $e );
          }
        }
      }
    }

?>