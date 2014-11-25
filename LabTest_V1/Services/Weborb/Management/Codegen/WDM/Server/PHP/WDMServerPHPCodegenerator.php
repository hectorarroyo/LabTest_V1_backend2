<?php
/*******************************************************************
 * WDMServerPHPCodegenerator.php
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
require_once("Domain/WDMServerPHPDomainEnviromentCodegenerator.php");
require_once("Domain/WDMServerPHPDomainCodegenerator.php");
require_once("Data/WDMServerPHPDataEnviromentCodegenerator.php");
require_once("Data/WDMServerPHPDataDatabaseCodegenerator.php");
require_once("Data/WDMServerPHPDataCodegenerator.php");
class WDMServerPHPCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		/*
		$this->generatePart(new WDMServerPHPDomainEnviromentCodegenerator());
		$this->generatePart(new WDMServerPHPDataEnviromentCodegenerator());
		$this->generatePart(new WDMServerPHPDataDatabaseCodegenerator());
*/
		/*
		$this->generatePart(new WDMServerPHPDomainCodegenerator());
		$this->generatePart(new WDMServerPHPDataCodegenerator());
		*/

		// ------------------ generate Config file
		$this->writeStartFile($this->meta->getUserDataModel()->Name."Config.php");
		$this->writeText("
<?php
	class ".$this->meta->getUserDataModel()->Name."Config
	{
		const LOGIN = \"".$this->meta->getUserDataModel()->getServerConnection()->Connection->UserName."\";
		const PASSWORD = \"".$this->meta->getUserDataModel()->getServerConnection()->Connection->Password."\";
		const DATABASE = \"".$this->meta->getUserDataModel()->getServerConnection()->DatabaseName."\";
		const HOST = \"".$this->meta->getUserDataModel()->getServerConnection()->Connection->HostName."\";
		const PORT = \"".$this->meta->getUserDataModel()->getServerConnection()->Connection->Port."\";
	}
?>");
		
		$this->writeEndFile();
		// ------------------ generate php files
		foreach ($this->meta->tables as $nameTable=>$tableMeta)
		{
			
			//$this->generatePart(new WDMClientDomainCodegenCodegenerator($nameTable));
			$this->generatePart(new WDMServerPHPDomainCodegenerator("",$nameTable));
			//$this->generatePart(new WDMServerPHPDataCodegenerator());
		}
		
		// ------------------ generate php files to Codegen folder
		$this->writeStartFolder("Codegen");

		foreach ($this->meta->tables as $nameTable=>$tableMeta)
		{
			$this->generatePart(new WDMServerPHPDomainCodegenerator("_",$nameTable));
			//$this->generatePart(new WDMServerPHPDataCodegenerator());
		}
		//$this->generatePart(new WDMClientDomainCodegenDataMapperRegistryCodegenerator());
		$this->writeEndFolder();
	}
	
}
?>