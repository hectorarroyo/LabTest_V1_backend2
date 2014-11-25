<?php
/*******************************************************************
 * MSSQLWDMMetaInspector.php
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
class MSSQLWDMMetaInspector extends WDMMetaInspector
{
	public function __construct()
	{
		$this->asType["bigint"] = "Number";
		$this->asType["binary"] = "ByteArray";
		$this->asType["bit"] = "Boolean";
		$this->asType["char"] = "String";		
		$this->asType["datetime"] = "Date";		
		$this->asType["decimal"] = "Number";		
		$this->asType["float"] = "Number";
		$this->asType["image"] = "ByteArray";
		$this->asType["int"] = "int";		
		$this->asType["money"] = "Number";		
		$this->asType["nchar"] = "String";		
		$this->asType["ntext"] = "String";		
		$this->asType["numeric"] = "Number";
		$this->asType["nvarchar"] = "String";
		$this->asType["real"] = "Number";		
		$this->asType["smalldatetime"] = "Date";		
		$this->asType["smallint"] = "Number";		
		$this->asType["smallmoney"] = "Number";
		$this->asType["sql_variant"] = "ByteArray";
		$this->asType["text"] = "String";
		$this->asType["timestamp"] = "ByteArray";
		$this->asType["tinyint"] = "Number";
		$this->asType["varbinary"] = "ByteArray";
		$this->asType["varchar"] = "String";
		$this->asType["uniqueidentifier"] = "String";
		$this->asType["xml"] = 'XML';

        $this->binaryTypes[] = "image";
        $this->binaryTypes[] = "sql_variant";
        $this->binaryTypes[] = "varbinary";
        $this->binaryTypes[] = "binary";
        $this->binaryTypes[] = "ByteArray";
                
	}

	public function getPrimitiveValue(/*string*/ $dataType)
	{
		 if ($dataType == "bit")
              return "true";
          else if ($dataType == "uniqueidentifier")
              return "UIDUtil.createUID()";
          else if ($dataType == "smalldatetime" || $dataType == "datetime")
              return "new Date()";
          else if ($this->IsBinary($dataType))
              return "new ByteArray()";
          else if ( $dataType == "nchar" ||
          			$dataType == "char" ||
          			$dataType == "nvarchar" ||
          			$dataType == "ntext" ||
          			$dataType == "text" ||
          			$dataType == "varchar")
              return "getRandomString()";
         elseif ($dataType == 'xml')
         	return 'new XML()';

          return "getRandomNumber()";
	}
}
?>