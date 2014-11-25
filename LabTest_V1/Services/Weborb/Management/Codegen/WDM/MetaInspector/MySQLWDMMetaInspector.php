<?php
/*******************************************************************
 * MySQLWDMMetaInspector.php
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
require_once("WDMMetaInspector.php");
class MySQLWDMMetaInspector extends WDMMetaInspector
{
	public function __construct()
	{
		$this->asType["tinyint"] = "Number";
		$this->asType["mediumint"] = "Number";
		$this->asType["bit"] = "Boolean";
		$this->asType["bool"] = "Boolean";
		$this->asType["smallint"] = "Number";
		$this->asType["int"] = "int";
		$this->asType["integer"] = "int";
		$this->asType["bigint"] = "Number";
		$this->asType["float"] = "Number";
		$this->asType["double"] = "Number";
		$this->asType["double precision"] = "Number";
		$this->asType["real"] = "Number";
		$this->asType["decimal"] = "Number";
		$this->asType["dec"] = "Number";
		$this->asType["numeric"] = "Number";
		$this->asType["date"] = "Date";
		$this->asType["datetime"] = "Date";
		$this->asType["timestamp"] = "Date";
		$this->asType["time"] = "int";
		$this->asType["year"] = "int";
		$this->asType["char"] = "String";
		$this->asType["varchar"] = "String";
		$this->asType["tinyblob"] = "ByteArray";
		$this->asType["tinytext"] = "ByteArray";
		$this->asType["blob"] = "ByteArray";
		$this->asType["text"] = "String";
		$this->asType["mediumblob"] = "ByteArray";
		$this->asType["mediumtext"] = "String";
		$this->asType["longblob"] = "ByteArray";
		$this->asType["longtext"] = "String";
		$this->asType["double unsigned"] = "Number";
		$this->asType["enum"] = "String";
		$this->asType["set"] = "String";

        $this->binaryTypes[] = "blob";
        $this->binaryTypes[] = "tinyblob";
        $this->binaryTypes[] = "mediumblob";
        $this->binaryTypes[] = "longblob";
	}

	public function getPrimitiveValue(/*string*/ $dataType)
	{
		 if ($dataType == "bit")
	        return "true";
         else if ($dataType == "timestamp" || $dataType == "datetime" || $dataType == "date")
             return "new Date()";
         else if ($this->IsBinary($dataType))
             return "new ByteArray()";
         else if ($dataType == "char" ||
         		  $dataType == "varchar" ||
         		  $dataType == "text" ||
         		  $dataType == "mediumtext" ||
         		  $dataType == "longtext")
             return "getRandomString()";

         return "getRandomNumber()";
	}
}
?>