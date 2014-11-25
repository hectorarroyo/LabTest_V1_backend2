<?php
/*******************************************************************
 * AMFMessageFactory.php
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



require_once(WebOrb . "Protocols/IMessageFactory.php");
require_once(WebOrb . "Message/Header.php");
require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Message/Body.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Datatypes.php");
require_once(WebOrb . "Writer/AmfFormatter.php");
require_once(WebOrb . "Writer/AmfV3Formatter.php");

require_once(WebOrb . "Reader/FlashorbBinaryReader.php");
require_once(WebOrb . "Reader/NumberReader.php");
require_once(WebOrb . "Reader/BooleanReader.php");
require_once(WebOrb . "Reader/UTFStringReader.php");
require_once(WebOrb . "Reader/AnonymousObjectReader.php");
require_once(WebOrb . "Reader/NullReader.php");
require_once(WebOrb . "Reader/UndefinedTypeReader.php");
require_once(WebOrb . "Reader/PointerReader.php");
require_once(WebOrb . "Reader/BoundPropertyBagReader.php");
require_once(WebOrb . "Reader/NotAReader.php");
require_once(WebOrb . "Reader/ArrayReader.php");
require_once(WebOrb . "Reader/DateReader.php");
require_once(WebOrb . "Reader/LongUTFStringReader.php");
require_once(WebOrb . "Reader/RemoteReferenceReader.php");
require_once(WebOrb . "Reader/XmlDataReader.php");
require_once(WebOrb . "Reader/NamedObjectReader.php");
require_once(WebOrb . "Reader/IntegerReader.php");

require_once(WebOrb . "Reader/V3ArrayReader.php");
require_once(WebOrb . "Reader/V3ByteArrayReader.php");
require_once(WebOrb . "Reader/V3DateReader.php");
require_once(WebOrb . "Reader/V3ObjectReader.php");
require_once(WebOrb . "Reader/V3Reader.php");
require_once(WebOrb . "Reader/V3StringReader.php");
require_once(WebOrb . "Reader/V3XmlReader.php");
require_once(WebOrb . "Reader/V3Reader.php");

class AmfMessageFactory
    implements IMessageFactory
{

    private static $s_readersV1 = null;
    private static $s_readersV3 = null;

    private static function getReadersV1()
    {
        if (is_null(self::$s_readersV1))
        {
            self::$s_readersV1 = array();
            self::$s_readersV1[Datatypes::NUMBER_DATATYPE_V1]           = new NumberReader();
            self::$s_readersV1[Datatypes::BOOLEAN_DATATYPE_V1]          = new BooleanReader();
            self::$s_readersV1[Datatypes::UTFSTRING_DATATYPE_V1]        = new UTFStringReader();
            self::$s_readersV1[Datatypes::OBJECT_DATATYPE_V1]           = new AnonymousObjectReader();
            self::$s_readersV1[Datatypes::NULL_DATATYPE_V1]             = new NullReader();
            self::$s_readersV1[Datatypes::UNKNOWN_DATATYPE_V1]          = new UndefinedTypeReader();
            self::$s_readersV1[Datatypes::POINTER_DATATYPE_V1]          = new PointerReader();
            self::$s_readersV1[Datatypes::OBJECTARRAY_DATATYPE_V1]      = new BoundPropertyBagReader();
            self::$s_readersV1[Datatypes::ENDOFOBJECT_DATATYPE_V1]      = new NotAReader();
            self::$s_readersV1[Datatypes::ARRAY_DATATYPE_V1]            = new ArrayReader();
            self::$s_readersV1[Datatypes::DATE_DATATYPE_V1]             = new DateReader();
            self::$s_readersV1[Datatypes::LONGUTFSTRING_DATATYPE_V1]    = new LongUTFStringReader();
            self::$s_readersV1[Datatypes::REMOTEREFERENCE_DATATYPE_V1]  = new RemoteReferenceReader();
            self::$s_readersV1[Datatypes::RECORDSET_DATATYPE_V1]        = null;
            self::$s_readersV1[Datatypes::PARSEDXML_DATATYPE_V1]        = new XmlDataReader();
            self::$s_readersV1[Datatypes::NAMEDOBJECT_DATATYPE_V1]      = new NamedObjectReader();
            self::$s_readersV1[Datatypes::V3_DATATYPE]                  = new V3Reader();
        }
        return self::$s_readersV1;
    }

    private static function getReadersV3()
    {
        if (is_null(self::$s_readersV3))
        {
            self::$s_readersV3 = array();
            self::$s_readersV3[Datatypes::UNKNOWN_DATATYPE_V3]      = new UndefinedTypeReader();
            self::$s_readersV3[Datatypes::NULL_DATATYPE_V3]         = new NullReader();
            self::$s_readersV3[Datatypes::BOOLEAN_DATATYPE_FALSEV3] = new BooleanReader(FALSE);
            self::$s_readersV3[Datatypes::BOOLEAN_DATATYPE_TRUEV3]  = new BooleanReader(TRUE);
            self::$s_readersV3[Datatypes::INTEGER_DATATYPE_V3]      = new IntegerReader();
            self::$s_readersV3[Datatypes::DOUBLE_DATATYPE_V3]       = new NumberReader();
            self::$s_readersV3[Datatypes::UTFSTRING_DATATYPE_V3]    = new V3StringReader();
            self::$s_readersV3[Datatypes::XML_DATATYPE_V3]          = new V3XmlReader();
            self::$s_readersV3[Datatypes::DATE_DATATYPE_V3]         = new V3DateReader();
            self::$s_readersV3[Datatypes::ARRAY_DATATYPE_V3]        = new V3ArrayReader();
            self::$s_readersV3[Datatypes::OBJECT_DATATYPE_V3]       = new V3ObjectReader();
            self::$s_readersV3[Datatypes::LONGXML_DATATYPE_V3]      = new V3XmlReader();
            self::$s_readersV3[Datatypes::BYTEARRAY_DATATYPE_V3]    = new V3ByteArrayReader();
        }
        return self::$s_readersV3;
    }

    public static function readData(FlashorbBinaryReader $reader, $context, $dataType)
    {
        $ctx = $context;

        $contextPassedAsNull = is_null($ctx);
        $dataTypePassedAsNull = is_null($dataType);

        if ($contextPassedAsNull)
        {
            $ctx = new ParseContext(0);
        }

        if ($dataTypePassedAsNull)
        {
            $type = $reader->readByte();
        }
        else
        {
            $type = $dataType;
        }
        
        if(($contextPassedAsNull && $dataTypePassedAsNull) || $ctx->getVersion() != 3)
            $version = 1;
        else
            $version = 3;
            
        Log::log(LoggingConstants::SERIALIZATION, "Reading data type: $type, version: $version");

        $readers = null;
		//echo $ctx->getVersion(); exit;
        if($version == 1)
            $readers = self::getReadersV1();
        else
            $readers = self::getReadersV3();

        return $readers[$type]->read($reader, $ctx);
    }

    public function canParse($contentType)
    {
        // return "application/x-amf" == strtolower($contentType);
        return true;
    }

    public function parse($stream)
    {
        return $this->readMessage($stream);
    }

    public function readMessage($stream)
    {
        Log::log(LoggingConstants::DEBUG, "Called");
        Log::log(LoggingConstants::INFO, "Content-Length:" . strlen($stream));

        $reader = new FlashorbBinaryReader($stream);

        $version = $reader->readUnsignedShort();
        $headersCount = $reader->readUnsignedShort();

        Log::log(LoggingConstants::DEBUG, "Version:$version, headers count: $headersCount");

        $headers = array();

        for($i = 0; $i < $headersCount; $i++)
        {
            Log::log(LoggingConstants::DEBUG, "Reading header $i");

            $headers[$i] = $this->readHeader($reader);

            Log::log(LoggingConstants::DEBUG, "Header info:" . $headers[$i]->toString());
        }

        $bodyCount = $reader->readUnsignedShort();

        Log::log(LoggingConstants::DEBUG, "Body count: $bodyCount");

        $body = array();

        for($i = 0; $i < $bodyCount; $i++)
        {
            Log::log(LoggingConstants::DEBUG, "Reading body $i");

            $body[$i] = $this->readBodyPart($reader);
        }

        $request = new Request($version, $headers, $body);

        $request->setFormatter((3 == $version)? new AmfV3Formatter(): new AmfFormatter());

        return $request;
    }

    private function readHeader(FlashorbBinaryReader $reader)
    {
        Log::log(LoggingConstants::DEBUG, "Called");

        $nameLength = $reader->readUnsignedShort();
        $name = $reader->readBytes($nameLength);
        $mustUnderstand = $reader->readBoolean();
        $length = $reader->readInteger();

        //$reader->readBytes($length);

        return new Header($name,
            $mustUnderstand,
            $length,
            self::readData($reader, null, null));
    }

    private function readBodyPart(FlashorbBinaryReader $reader)
    {
        Log::log(LoggingConstants::DEBUG, "Called");
        $serviceURI = $reader->readUTF();
        $responseURI = $reader->readUTF();
        $bodyLength = $reader->readInteger();

        Log::log(LoggingConstants::INFO, "Service:$serviceURI, responseURI:$responseURI, length: $bodyLength");

        return new Body($serviceURI,
            $responseURI,
            $bodyLength,
            self::readData($reader, null, null));
    }

}

?>
