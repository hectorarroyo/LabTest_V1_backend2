<?php
/*******************************************************************
 * Request.php
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

require_once(WebOrb . "Message/Body.php");
require_once(WebOrb . "Message/Header.php");
require_once(WebOrb . "Writer/IProtocolFormatter.php");

class Request
{
    private $m_version;
    private $m_headers = array();
    private $m_bodyParts = array();
    private $m_currentBodyIndex;
    private $m_responseBodies = array();
    private $m_formatter;

    public function __construct($version, $headers, $bodyParts)
    {
      $this->m_version = $version;
      $this->m_headers = $headers;
      $this->m_bodyParts = $bodyParts;
      $this->m_currentBodyIndex = 0;
    }

    public function getHeader($index)
    {
        return $this->m_header[$index];
    }

    public function getVersion()
    {
        return $this->m_version;
    }

    public function getBodyCount()
    {
      return sizeof($this->m_bodyParts);
    }

    public function getResponseBodies()
    {
      return $this->m_bodyParts;
    }

    public function getResponseHeaders()
    {
      return array();
    }

    public function setCurrentBody($index)
    {
      $this->m_currentBodyIndex = $index;
    }

    public function getRequestURI()
    {
      return $this->currentBody()->getServiceURI();
    }

    public function setResponseBodyPart($object)
    {
      $this->currentBody()->setResponseDataObject($object);
    }

    public function setResponseURI($responseUri)
    {
		$this->currentBody()->setResponseURI(
            $this->currentBody()->getResponseURI() . $responseUri);
	}


    public function setFormatter(IProtocolFormatter $formatter)
    {
	  $this->m_formatter = $formatter;
	}

    public function getFormatter()
    {
	  return $this->m_formatter;
	}

	public function getRequestBodyData()
	{
		return $this->currentBody()->getDataObject();
      
    }


    private function currentBody()
    {
      return $this->m_bodyParts[$this->m_currentBodyIndex];
    }
}

?>
