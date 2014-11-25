<?php
/*******************************************************************
 * WDMClientDomainDataMapperRegistryCodegenerator.php
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
class WDMClientDomainDataMapperRegistryCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFile("DataMapperRegistry.as");

		$this->writeText("
      package ".$this->meta->getClientNamespace()."
      {
        import ".$this->meta->getClientNamespace().".Codegen._DataMapperRegistry;

        public class DataMapperRegistry extends _DataMapperRegistry
        {
          private static var s_instance:DataMapperRegistry;
          public static function get Instance():DataMapperRegistry
          {
	          if(s_instance == null)
		          s_instance = new DataMapperRegistry();
        				
	          return s_instance;
          } 
        }
      }");
		
		$this->writeEndFile();
	}
}
?>