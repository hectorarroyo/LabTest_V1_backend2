<?php
/*******************************************************************
 * V3ByteArrayReader.php
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
require_once(WebOrb . "Reader/NumberObject.php");
require_once(WebOrb . "Reader/ArrayType.php");

class V3ByteArrayReader
    implements ITypeReader
{

    public function read(FlashorbBinaryReader $reader, ParseContext $parseContext)
    {
        $refId = $reader->readVarInteger();

        if (($refId & 0x1) == 0)
        {
            return $parseContext->getReference($refId >> 1);
        }

        $bytes = $reader->readBytes($refId >> 1);
        $objArray = array();

        $len = strlen($bytes);
        for ($i = 0; $i < $len; $i ++)
        {
            $objArray[] = new NumberObject( ord($bytes[$i]));
        }

        $arrayType = new ArrayType($objArray);
        $parseContext->addReference($arrayType);
        return $arrayType;
    }

}

?>
