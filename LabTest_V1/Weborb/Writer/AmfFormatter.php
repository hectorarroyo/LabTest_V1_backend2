<?php
/*******************************************************************
 * AmfFormatter.php
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



require_once(WebOrb . "Writer/IProtocolFormatter.php");
require_once(WebOrb . "Writer/BaseFormatter.php");
require_once(WebOrb . "Writer/ObjectSerializer.php");
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util/ORBDateTime.php");
require_once(WebOrb . "Writer/ReferenceCache.php");

class AmfFormatter extends BaseFormatter implements IProtocolFormatter
{
    private $m_objectSerializer;
    private $m_referenceCache;

    public function __construct()
    {
        $this->m_objectSerializer = new ObjectSerializer();
        $this->m_referenceCache = new ReferenceCache();
        MessageWriter::addTypeWriter( 'string', new StringWriter( false ) );
    }

    public function beginWriteArray($length)
    {
        parent::write(10);
        parent::writeInt($length);
    }

    public function beginWriteFieldValue()
    {
    }

    public function beginWriteMessage(Request $message)
    {
    }

    public function beginWriteNamedObject($objectName, $fieldCount = null)
    {
        parent::write(0x10);
        $this->directWriteString($objectName);
    }

    public function beginWriteObject($fieldCount = null)
    {
        parent::write(3);
    }

    public function beginWriteObjectMap($size)
    {
        parent::write(8);
        parent::writeInt($size);
    }

    public function cleanup()
    {
    }

    public function directWriteBoolean($value)
    {
        parent::write($value);
    }

    public function directWriteInt($value)
    {
        parent::writeInt($value);
    }

    public function directWriteShort($value)
    {
        parent::writeShort($value);
    }

    public function directWriteString($value)
    {
        parent::writeUtf($value);
    }

    public function endWriteArray()
    {
    }

    public function endWriteFieldValue()
    {
    }

    public function endWriteMessage()
    {
    }

    public function endWriteNamedObject()
    {
        parent::write(0);
        parent::write(0);
        parent::write(9);
    }

    public function endWriteObject()
    {
        parent::write(0);
        parent::write(0);
        parent::write(9);
    }

    public function endWriteObjectMap()
    {
        parent::write(0);
        parent::write(0);
        parent::write(9);
    }


    public function getContentType()
    {
        return "application/x-amf";
    }

    public function writeBoolean($value)
    {
        parent::write(1);
        parent::write($value);
    }

    public function writeDate(ORBDateTime $datetime)
    {
        parent::write(11);
        parent::writeDouble((float) ($datetime->getTotalMs()));
        parent::writeShort((int) ($datetime->getTimeZone()));
    }

    public function writeFieldName($value)
    {
        parent::writeUtf($value);
    }

    public function writeMessageVersion($value)
    {
        parent::writeShort((int) $value);
    }

    public function writeNull()
    {
        parent::write(5);
    }

    public function writeNumber($value)
    {
        parent::write(0);

        parent::writeDouble(floatval($value));
    }

    public function writeReference($refId)
    {
        parent::write(7);
        parent::writeShort($refId);
    }

    public function writeString($str)
    {
        $length = strlen($str);

        if($length < 65536)
        {
            parent::write(2);
        }
        else
        {
            parent::write(12);
        }

        parent::writeUtf($str);
    }

    public function writeXML($xmlString)
    {
    	parent::write(15);
    	parent::writeUtf($xmlString);	
    }
    
    public function getObjectSerializer()
    {
        return $this->m_objectSerializer;
    }

    public function getReferenceCache()
    {
      return $this->m_referenceCache;
    }

    public function resetReferenceCache()
    {
      return $this->m_referenceCache->reset();
    }

    public function beginWriteBodyContent()
    {

    }

    public function endWriteBodyContent(){}

    public function writeArrayReference($refId)
    {
      $this->writeReference($refId);
    }

}

?>
