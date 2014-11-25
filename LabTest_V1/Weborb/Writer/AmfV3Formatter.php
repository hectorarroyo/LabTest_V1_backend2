<?php
/*******************************************************************
 * AmfV3Formatter.php
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
require_once(WebOrb . "Writer/V3ObjectSerializer.php");
require_once(WebOrb . "Writer/V3ReferenceCache.php");
require_once(WebOrb . "Writer/MessageWriter.php");
require_once(WebOrb . "Writer/DateWriter.php");
require_once(WebOrb . "Writer/StringWriter.php");
require_once(WebOrb . "Writer/ORBDateTimeWriter.php");
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util/Datatypes.php");
require_once(WebOrb . "Util/ORBDateTime.php");

class AmfV3Formatter extends BaseFormatter implements IProtocolFormatter
{
    private $m_objectSerializer;
    private $m_referenceCache;

    public function __construct()
    {	
        $this->m_objectSerializer = new V3ObjectSerializer();
        $this->m_referenceCache = new V3ReferenceCache();
        MessageWriter::addTypeWriter( 'DateTime', new DateWriter( TRUE ) );
        MessageWriter::addTypeWriter( 'ORBDateTime', new ORBDateTimeWriter( TRUE ) );
        MessageWriter::addTypeWriter( 'string', new StringWriter( TRUE ) );
    }

    public function getReferenceCache()
    {
        return $this->m_referenceCache;
    }

    public function resetReferenceCache()
    {
        $this->m_referenceCache->reset();
    }

    public function directWriteString($str)
    {
        parent::writeUTF($str);
    }

    public function directWriteInt($i)
    {
        parent::writeInt($i);
    }
    
	public function directWriteVarInt($i)
    {
        parent::writeVarInt($i);
    }
    

    public function directWriteBoolean($b)
    {
        parent::write($b);

            if( $b )
                parent::write( Datatypes::BOOLEAN_DATATYPE_TRUEV3 );
            else
                parent::write( Datatypes::BOOLEAN_DATATYPE_FALSEV3 );
    }

    public function directWriteShort($s)
    {
        parent::writeShort($s);
    }
	
	public function directWriteBytes( /*byte[]*/ $b )
	{
	       	//foreach($b as $val)
	    	parent::write( $b); 	   
	}    
    
    public function beginWriteMessage(Request $message)
    {
    }

    public function endWriteMessage()
    {
    }

    public function writeMessageVersion($version)
    {
        parent::writeShort($version);
    }

    public function beginWriteBodyContent()
    {
        parent::write(Datatypes::V3_DATATYPE);
    }

    public function endWriteBodyContent()
    {

    }

    public function beginWriteArray($length)
    {
        parent::write(Datatypes::ARRAY_DATATYPE_V3);
        parent::writeVarInt($length << 1 | 0x1);
        parent::writeVarInt(0x1);
    }

    public function endWriteArray()
    {

    }

    public function writeBoolean($b)
    {
        if ($b)
        {
            parent::write(Datatypes::BOOLEAN_DATATYPE_TRUEV3);
        }
        else
        {
            parent::write(Datatypes::BOOLEAN_DATATYPE_FALSEV3);
        }
    }

    public function writeDate(ORBDateTime $datetime)
    {
        parent::write(Datatypes::DATE_DATATYPE_V3);
        parent::writeVarInt(0x1);
        parent::writeDouble($datetime->getTotalMs());
    }

    public function beginWriteObjectMap($size)
    {

        parent::write(Datatypes::OBJECT_DATATYPE_V3);
        parent::writeVarInt(0x3 | size << 4); // classInfo with size of the property count
        parent::writeVarInt(1); // no classname
    }

    public function endWriteObjectMap()
    {

    }

    public function writeFieldName($s)
    {
    	
    	$this->writeStringOrReferenceId($s);
        //writeUTF($s, true);
    }
	
	private function writeStringOrReferenceId( /*String*/ $s )
	{
	    try
	    {
	      if( $this->m_referenceCache->hasObject( $s, true ) )
	      {
	      
	        /*short*/ $refId = $this->m_referenceCache->getId( $s );
	      	
	        parent::writeVarInt( $refId << 1);
	        
	        
	      }
	      else
	      {
	      	$this->m_referenceCache->addObject( $s );
	      	parent::writeUTF($s, true );
	      }
	    }
	    catch( Exception $e )
	    {
	     	Log::log( LoggingConstants::EXCEPTION, $e->getMessage() );
	    }
	}
    
    public function beginWriteFieldValue()
    {
    }

    public function endWriteFieldValue()
    {
    }

    public function writeNull()
    {
        parent::write(Datatypes::NULL_DATATYPE_V3);
    }

    public function writeNumber($number)
    {
        if (($number >= -268435456 && $number <= 268435455)
                && !is_double($number))
        {
            parent::write(Datatypes::INTEGER_DATATYPE_V3);
            parent::writeVarInt((integer) $number & 0x1fffffff);
        }
        else
        {
            parent::write(Datatypes::DOUBLE_DATATYPE_V3);
            parent::writeDouble($number);
        }
    }

    public function beginWriteNamedObject($objectName, $fieldCount = null)
    {
        parent::write(Datatypes::OBJECT_DATATYPE_V3);
        parent::writeVarInt(0x3 | $fieldCount << 4);

        if (is_null($objectName))
        {
            parent::writeVarInt(1);
        }
        else
        {
        	
        	$this->writeStringOrReferenceId( $objectName );
            //writeUTF($objectName, true);
        }
        $this->m_referenceCache->AddToTraitsCache( $objectName );
    }

    public function endWriteNamedObject()
    {

    }

    public function beginWriteObject($fieldCount = null)
    {
        parent::write(Datatypes::OBJECT_DATATYPE_V3);
        parent::writeVarInt(0x3 | fieldCount << 4);
        parent::writeVarInt(0x1);
    }

    public function endWriteObject()
    {
    }

    public function writeArrayReference($refID)
    {
        parent::write(Datatypes::ARRAY_DATATYPE_V3);
        parent::writeVarInt($refID << 1);
    }

    public function writeObjectReference($refID)
    {
        parent::write(Datatypes::OBJECT_DATATYPE_V3);
        parent::writeVarInt($refID << 1);
    }

    public function writeStringReference($refID)
    {
    	parent::write( Datatypes::UTFSTRING_DATATYPE_V3);
        parent::writeVarInt($refID << 1);
    }

    public function writeString($s)
    {
    	
        parent::write(Datatypes::UTFSTRING_DATATYPE_V3);
        parent::writeUTF($s, true);
        
    }

    public function writeXML($xmlString)
    {
    	parent::write(Datatypes::LONGXML_DATATYPE_V3);
    	parent::writeUtf($xmlString, true);	
    }
    
    public function cleanup()
    {
        //$this->m_writer = "";
    }

    public function getContentType()
    {
        return "application/x-amf";
    }

    public function getObjectSerializer()
    {
        return $this->m_objectSerializer;
    }

    public function writeReference($refId)
    {

    }

}

?>
