<?php
/*******************************************************************
 * V3ArrayReader.php
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



require_once(WebOrb . "Reader/ITypeReader.php");
require_once(WebOrb . "Reader/FlashorbBinaryReader.php");
require_once(WebOrb . "Reader/ParseContext.php");
require_once(WebOrb . "Reader/ReaderUtils.php");
require_once(WebOrb . "Reader/AnonymousObject.php");
require_once(WebOrb . "Protocols/AMFMessageFactory.php");

class V3ArrayReader
    implements ITypeReader
{

    public function read(FlashorbBinaryReader $reader, ParseContext $parseContext)
    {
        $refId = $reader->readVarInteger();

        if (($refId & 0x1) == 0)
        {
            return $parseContext->getReference($refId >> 1);
        }

        $arraySize = $refId >> 1;
        $adaptingType = null;
        $container = null;

        while (true)
        {
            $str = ReaderUtils::readString($reader, $parseContext);

            if (is_null($str) || strlen($str) == 0)
            {
                break;
            }

            if (is_null($container))
            {
                $container = array();
                $adaptingType = new AnonymousObject($container);
                $parseContext->addReference($adaptingType);
            }

            $container[$str] = AmfMessageFactory::readData($reader, $parseContext, null);
        }

        if (is_null($adaptingType))
        {
            $container = array();
            $adaptingType = new ArrayType($container);
            $parseContext->addReference($adaptingType);

            for ($i = 0; $i < $arraySize; $i ++)
            {
                $container[$i] = AmfMessageFactory::readData($reader, $parseContext, null);
            }
        }
        else
        {
            for ($i = 0; $i < $arraySize; $i ++)
            {
                $obj = AmfMessageFactory::readData($reader, $parseContext, null);
                $container[(string) $i] = $obj;
            }
        }

        return $adaptingType;
    }

}

?>
