<?php
/*******************************************************************
 * DataTableAsListWriter.php
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
 
require_once(WebOrb . "Util/IDatabaseReader.php");
require_once(WebOrb . "Reader/Dataset/RemotingDataSet.php");
 
class DataTableAsListWriter
    implements ITypeWriter
{
    public function isReferenceableType()
    {
        return false;
    }

    public function write(&$obj, IProtocolFormatter $writer)
    {
        $dbReader = DatabaseReaderFactory::getReader($obj->getDatabaseName());
        
        if($dbReader == null)
        {
            Log::log(LoggingConstants::ERROR,"Database reader for " . $databaseName . " not found");
            
            $writer->writeNull();
            
            return;
        }
        
        $arr = array();
    
        while($object = $dbReader->readObject($obj->getResource()))
            $arr[] = $object;
            
        if($writer instanceof AmfV3Formatter)
            MessageWriter::writeObject($arr,$writer);
        else
        {
            $remotingDataSet = new RemotingDataSet($arr);
        
            MessageWriter::writeObject($remotingDataSet->getDataSetInfo(),$writer);            
        } 
    }
}

?>