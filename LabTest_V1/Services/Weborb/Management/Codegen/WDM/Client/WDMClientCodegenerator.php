<?php
/*******************************************************************
 * WDMClientCodegenerator.php
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

require_once("Domain/Codegen/WDMClientDomainCodegenCodegenerator.php");
require_once("Domain/Codegen/WDMClientDomainCodegenDataMapperRegistryCodegenerator.php");
require_once("Domain/WDMClientDomainCodegenerator.php");
require_once("Domain/WDMClientDomainDataMapperRegistryCodegenerator.php");
require_once("Domain/WDMClientDomainActiveRecordsCodegenerator.php");
require_once("UI/TestDrive/WDMClientUITestDriveCodegenerator.php");
require_once("UI/TestDrive/WDMClientUITestDriveAddCodegenerator.php");
require_once("UI/TestDrive/WDMClientUITestDriveDatabaseViewCodegenerator.php");
require_once("UI/TestDrive/WDMClientUITestDriveAppCodegenerator.php");
require_once("UI/UnitTest/WDMClientUIUnitTestCodegenerator.php");
require_once("UI/UnitTest/WDMClientUIUnitTestAppCodegenerator.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php"); 

class WDMClientCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFolder($this->meta->getClientFolder());
			$this->writeStartFolder("Codegen");

				foreach ($this->meta->tables as $nameTable=>$tableMeta)
				{
					$this->generatePart(new WDMClientDomainCodegenCodegenerator($nameTable));
				}

				$this->generatePart(new WDMClientDomainCodegenDataMapperRegistryCodegenerator());
				
			$this->writeEndFolder();
			
			foreach ($this->meta->tables as $nameTable=>$tableMeta)
			{
				$this->generatePart(new WDMClientDomainCodegenerator($nameTable));
			}		
			
			$this->generatePart(new WDMClientDomainDataMapperRegistryCodegenerator());
			$this->generatePart(new WDMClientDomainActiveRecordsCodegenerator());

		$this->writeEndFolder();

		if ($this->meta->IsGenerateTestDrive() || $this->meta->IsGenerateUnitTests())
		{
			$this->writeStartFolder("UI");
			
			if ($this->meta->IsGenerateTestDrive())
			{
				$this->writeStartFolder("TestDrive");

					foreach ($this->meta->tables as $tableName=>$tableMeta)
					{
						$this->generatePart(new WDMClientUITestDriveCodegenerator($tableName));
						$this->generatePart(new WDMClientUITestDriveAddCodegenerator($tableName));						
					}
					
					$this->generatePart(new WDMClientUITestDriveDatabaseViewCodegenerator());

				$this->writeEndFolder();				
			}

			if ($this->meta->IsGenerateUnitTests())
			{
				$this->writeStartFolder("UnitTest");
					foreach ($this->meta->tables as $nameTable=>$tableMeta)
					$this->generatePart(new WDMClientUIUnitTestCodegenerator($nameTable));
				$this->writeEndFolder();				
			}

			$this->writeEndFolder();			
		}
		
		if ($this->meta->IsGenerateUnitTests())
			$this->generatePart(new WDMClientUIUnitTestAppCodegenerator());
		if ($this->meta->IsGenerateTestDrive())
			$this->generatePart(new WDMClientUITestDriveAppCodegenerator());
	}
}
?>