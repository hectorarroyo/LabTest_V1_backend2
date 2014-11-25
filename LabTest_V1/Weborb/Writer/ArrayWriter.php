<?php
/*******************************************************************
 * ArrayWriter.php
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



require_once(WebOrb . "Writer/ITypeWriter.php");
require_once(WebOrb . "Writer/IProtocolFormatter.php");
require_once(WebOrb . "Writer/MessageWriter.php");
require_once(WebOrb . "Util/Logging/Log.php");

class ArrayWriter
    implements ITypeWriter
{

    public function __construct()
    {
    }

    public function isReferenceableType()
    {
        return true;
    }

    public function write(&$obj, IProtocolFormatter $writer)
    {
        $writer->beginWriteArray(count($obj));
        $keys = array_keys($obj);
        $size = count($obj);
        for ($index = 0; $index < $size; $index ++)
            MessageWriter::writeObject($obj[$keys[$index]], $writer);
        
        $writer->endWriteArray();
    }

}

?>
