<?php
/*******************************************************************
 * V3ObjectReader.php
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
require_once(WebOrb . "Reader/ClassInfo.php");
require_once(WebOrb . "Reader/IAdaptingType.php");
require_once(WebOrb . "Reader/NamedObject.php");
require_once(WebOrb . "Reader/AnonymousObject.php");
require_once(WebOrb . "Reader/ReaderUtils.php");
require_once(WebOrb . "Protocols/AMFMessageFactory.php");

class V3ObjectReader
    implements ITypeReader
{

    public function read(FlashorbBinaryReader $reader, ParseContext $parseContext)
    {
    	$refId = $reader->readVarInteger();

        if (($refId & 0x1) == 0)
        {
            return $parseContext->getReference($refId >> 1);
        }

        $classInfo = $this->getClassInfo($refId, $reader, $parseContext);


        $props = array();
        $obj = new AnonymousObject($props);
        $returnValue = $obj;

        if (!is_null($classInfo->getClassName()) && strlen($classInfo->getClassName()) > 0)
        {
            $returnValue = new NamedObject($classInfo->getClassName(), $obj);
        }

        $parseContext->addReference($returnValue);
        $propCount = $classInfo->getPropertyCount();

        for ($i = 0; $i < $propCount; $i ++)
        {
            $props[$classInfo->getProperty($i)] = AmfMessageFactory::readData($reader, $parseContext, null);
        }

        if ($classInfo->getLooseProps())
        {
            while (TRUE)
            {
                $propName = ReaderUtils::readString($reader, $parseContext);

                if (is_null($propName) || strlen($propName) == 0)
                {
                    break;
                }

                $props[$propName] = AmfMessageFactory::readData($reader, $parseContext, null);
            }
        }

        return $returnValue;
    }

    private function getClassInfo($refId, FlashorbBinaryReader $reader, ParseContext $parseContext)
    {
        if (($refId & 0x3) == 1)
        {
            return $parseContext->getClassInfoReference($refId >> 2);
        }

        $looseProps = ($refId & 0x8) == 8;
        $className = ReaderUtils::readString($reader, $parseContext);
        $classInfo = new ClassInfo($looseProps, $className);

        $propsCount = $refId >> 4;

        Log::log(LoggingConstants::SERIALIZATION, "Readed class info, class: $className, property count:$propsCount");
          
        for ($i = 0; $i < $propsCount; $i ++)
        {
            $classInfo->addProperty(ReaderUtils::readString($reader, $parseContext));
        }

        $parseContext->addClassInfoReference($classInfo);

        return $classInfo;
    }

}

?>
