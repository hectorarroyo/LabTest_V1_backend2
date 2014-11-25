<?php
/*******************************************************************
 * ErrMessage.php
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


    require_once(WebOrb . "V3Types/AckMessage.php");

    class ErrMessage
            extends AckMessage
    {
		public $rootCause;
 	    public $faultString;
		public $faultCode = "Server.Processing";
		public $extendedData;
		public $faultDetail;

        private $m_authException;

		public function __construct( $correlationId, Exception $exception )
		{
			parent::__construct( $correlationId, null, null );

			$this->rootCause = $exception->getTraceAsString();
			$this->faultString = $exception->getMessage();

			if($exception instanceof ServiceException)
				$this->extendedData = $exception->getCode();
			else
				$this->extendedData = $exception->__toString();

			$this->faultDetail = $exception->__toString();
			$this->SetError();
			$this->m_authException = ($exception instanceof WebORBAuthenticationException);
		}

		public function isAuthentication()
		{
          return $this->m_authException;
        }
    }
?>