<?php
/*******************************************************************
 * IProtocolFormatter.php
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



require_once(WebOrb . "Message/Request.php");
require_once(WebOrb . "Util/ORBDateTime.php");

interface IProtocolFormatter
{

    function beginWriteArray($length);
    function beginWriteFieldValue();
    function beginWriteMessage(Request $message);
    function beginWriteNamedObject($objectName, $fieldCount = null);
    function beginWriteObject($fieldCount = null);
    function beginWriteObjectMap($size);
    function cleanup();
    function directWriteBoolean($b);
    function directWriteInt($i);
    function directWriteShort($s);
    function directWriteString($str);
    function endWriteArray();
    function endWriteFieldValue();
    function endWriteMessage();
    function endWriteNamedObject();
    function endWriteObject();
    function endWriteObjectMap();
    function getBytes();
    function getContentType();
    function writeBoolean($b);
    function writeDate(ORBDateTime $datetime);
    function writeFieldName($s);
    function writeMessageVersion($version);
    function writeNull();
    function writeNumber($number);
    function writeReference($refId);
    function writeString($s);
    function getObjectSerializer();
    function getReferenceCache();
    function resetReferenceCache();
    function beginWriteBodyContent();
    function endWriteBodyContent();
    function writeXML($xmlString);

}

?>
