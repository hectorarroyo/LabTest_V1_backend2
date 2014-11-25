<?php
/*******************************************************************
 * V3XmlReader.php
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
require_once(WebOrb . "Reader/XmlDataType.php");

class V3XmlReader
    implements ITypeReader
{

    public function read(FlashorbBinaryReader $reader, ParseContext $parseContext)
    { 
        $len = $reader->readVarInteger();

        if (($len & 0x1) == 0)
        {
            return $parseContext->getReference($len >> 1);
        }

        $len = $len >> 1;

        if ($len == 0)
        {
            return $this->parseString("");
        }

        $xmlStr = $reader->readUTF($len);
        $xmlType = $this->parseString($xmlStr);
        $parseContext->addReference($xmlType);
        return $xmlType;
    }

    private function parseString($xmlStr)
    {
        $document = new DomDocument();

        try
        {
            $document->loadXml($xmlStr);
        }
        catch (Exception $ex)
        {
        }

        return new XmlDataType($document);
    }

}

?>
