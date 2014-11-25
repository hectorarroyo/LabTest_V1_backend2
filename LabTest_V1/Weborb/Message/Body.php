<?php
/*******************************************************************
 * Body.php
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



require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");
require_once(WebOrb . "Reader/ArrayType.php");
require_once(WebOrb . "Reader/IAdaptingType.php");

class Body
{

    private $m_serviceURI;
    private $m_responseURI;
    private $m_length;
    private $m_dataObject;

    private $m_responseDataObject;

    public function __construct($serviceURI, $responseURI, $length, $dataObject)
    {
        Log::log(LoggingConstants::DEBUG, "serviceURI:" . $serviceURI);

        $this->m_serviceURI = $serviceURI;
        $this->m_responseURI = $responseURI;
        $this->m_length = $length;

        if($dataObject instanceof IAdaptingType)
        {
            if($dataObject instanceof ArrayType)
            {
                $this->m_dataObject = $dataObject->getArray();
            }
            else
            {
                $this->m_dataObject = array($dataObject);
            }
        }
        else
        {
            $this->m_dataObject = $dataObject;
        }
    }

    public function getServiceURI()
    {
        return $this->m_serviceURI;
    }

    public function getResponseURI()
    {
        return $this->m_responseURI;
    }

    public function getLength()
    {
        return $this->m_length;
    }

    public function getDataObject()
    {
        return $this->m_dataObject;
    }

    public function setDataObject($dataObject)
    {
        return $this->m_dataObject = $dataObject;
    }

    public function setResponseDataObject($value)
    {
        $this->m_responseDataObject = $value;
    }

    public function getResponseDataObject()
    {
        return $this->m_responseDataObject;
    }

    public function setResponseURI($responseUri)
    {
        $this->m_responseURI = $responseUri;
    }

}

?>
