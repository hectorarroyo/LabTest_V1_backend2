<?php
/*******************************************************************
 * ObjectSerializer.php
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



require_once(WebOrb . "Writer/IObjectSerializer.php");
require_once(WebOrb . "Writer/IProtocolFormatter.php");
require_once(WebOrb . "Writer/MessageWriter.php");
require_once(WebOrb . "Util/Logging/Log.php");

class ObjectSerializer
    implements IObjectSerializer
{

    public function writeObject($className, $objectFields, IProtocolFormatter $writer)
    {
        if (is_null($className))
        {
            $writer->beginWriteObjectMap(count($objectFields));
        }
        else
        {
            $writer->beginWriteNamedObject($className, count($objectFields));
        }

        $keys = array_keys($objectFields);

        $fieldCount = count($objectFields);
        for ($i = 0; $i < $fieldCount; $i ++)
        {
            $fieldName = $keys[$i];

            $writer->writeFieldName($fieldName);
            $writer->beginWriteFieldValue();

            try
            {
                MessageWriter::writeObject($objectFields[$fieldName], $writer);
            }
            catch (Exception $exception)
            {
                Log::logException(LoggingConstants::ERROR, "unable to serialize object's field " . $fieldName, $exception);
            }

            $writer->endWriteFieldValue();
        }

        if (is_null($className))
        {
            $writer->endWriteObjectMap();
        }
        else
        {
            $writer->endWriteNamedObject();
        }
    }

}

?>
