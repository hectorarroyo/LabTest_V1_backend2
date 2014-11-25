<?php
/*******************************************************************
 * WDMClientDomainActiveRecordsCodegenerator.php
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
class WDMClientDomainActiveRecordsCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFile("ActiveRecords.as");
		
		$this->writeText("
      package ".$this->meta->getClientNamespace()."
      {
        public final class ActiveRecords
        {");

		foreach ($this->meta->tables as $nameTable=>$tableMeta)
		{
			$className = $this->meta->getClassName($nameTable);
			$this->writeText("
            public static function get ".$className."():".$className."DataMapper
            {
              return DataMapperRegistry.Instance.".$className.";
            }\n");
		}
		
		$this->writeText("
        }
      }");
		
		$this->writeEndFile();
		
		
		$this->writeStartFile("".$this->meta->getClassName($this->meta->database->DatabaseName)."Db.as");
		
		$this->writeText("
      package ".$this->meta->getClientNamespace()."
      {
        import ".$this->meta->getClientNamespace().".Codegen._".$this->meta->getClassName($this->meta->database->DatabaseName)."Db;
        
        public final class ".$this->meta->getClassName($this->meta->database->DatabaseName)."Db extends _".$this->meta->getClassName($this->meta->database->DatabaseName)."Db
        {
          private static var _instance:".$this->meta->getClassName($this->meta->database->DatabaseName)."Db;
          
          public static function get Instance():".$this->meta->getClassName($this->meta->database->DatabaseName)."Db
          {
            if( _instance == null )
              _instance = new ".$this->meta->getClassName($this->meta->database->DatabaseName)."Db();
              
            return   _instance;
          }
        }
    }");
		
		$this->writeEndFile();		
	}
}
?>