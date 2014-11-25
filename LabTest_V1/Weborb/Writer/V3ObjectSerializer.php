<?php
/*******************************************************************
 * V3ObjectSerializer.php
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
require_once(WebOrb . "Writer/IObjectSerializer.php");
require_once(WebOrb . "Writer/IProtocolFormatter.php");
require_once(WebOrb . "Writer/MessageWriter.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Datatypes.php");

class V3ObjectSerializer
    implements IObjectSerializer
{

    function writeObject($className, $objectFields, IProtocolFormatter $writer)
    {
        unset($objectFields["_orbclassname"]);
        /*V3ReferenceCache*/ $cache = $writer->GetReferenceCache();
   		/*String*/ $traitsClassId = $className;

            if( $traitsClassId == null )
            {
                $str = "";
				$keys = array_keys($objectFields);
				$fieldCount = count($objectFields);
                for ($i = 0; $i < $fieldCount; $i++)
                {
                	$str .= $keys[$i] . "-";
                }

                $traitsClassId = $str;
            }
        if($cache->HasTraits( $traitsClassId ) )
        {
        	/*MemoryStream*/ $stream = "";
            /*int*/ $traitId = $cache->GetTraitsId( $traitsClassId );
			$writer->directWriteBytes( Datatypes::OBJECT_DATATYPE_V3 );	
            $writer->directWriteVarInt(0x1 | $traitId << 2);
       	}
        else
        {
	        $writer->beginWriteNamedObject($className, count($objectFields));
	        
	        if( $className == null )
                    $cache->AddToTraitsCache( $traitsClassId );
	
	        $keys = array_keys($objectFields);	
	        $fieldCount = count($objectFields);
	        
	        for ($i = 0; $i < $fieldCount; $i ++)
	        {
	            $fieldName = $keys[$i];
	
	            $writer->writeFieldName($fieldName);
	        }
        }
		        
        $keys = array_keys($objectFields);
        $fieldsCount = count($objectFields);
        
        for ($i = 0; $i < $fieldsCount; $i++)
        {
            $fieldName = $keys[$i];

            $writer->beginWriteFieldValue();

            try
            {
                MessageWriter::writeObject($objectFields[$fieldName], $writer);
            }
            catch (Exception $exception)
            {
                Log::log(LoggingConstants::ERROR, "unable to serialize object's field " . $fieldName, $exception);
            }

            $writer->endWriteFieldValue();

        }

        $writer->endWriteNamedObject();
    }

}

?>
