<?php
/*******************************************************************
 * FlashCommCodegenerator.php
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

require_once("ServiceCodegenerator.php");

class FlashCommCodegenerator extends ServiceCodegenerator
{			
	protected function doGenerate()
	{
		$parent = $this->service->Parent;
		
		if($parent == null)
		{
			$this->writeStartFile($this->service->Name.".as");
			$this->createCode();
			$this->writeEndFile();	
			$this->createVO();
		}
		else
		{
			$allParents = array();
			$allParents[] = $parent;
			
			while($parent->Parent != null)
			{					
				$parent = $parent->Parent;
				$allParents[] = $parent;
			}
			$allParents = array_reverse($allParents);
						
			foreach($allParents as $parent)
			{
				$this->writeStartFolder($parent->Name);
				$this->package.=$parent->Name.".";
			}
			
			$this->serviceParents = $allParents;
			$this->writeStartFile($this->service->Name.".as");
			$this->createCode();
			$this->writeEndFile();		
			$this->createVO();							
		}						
	}
		
	protected function writeHeader($name)
	{
	}
	
	protected function createVOCode($vars)
	{
		$pieces = explode('.',$this->file->Name);
		$this->writeLine("\t  package " . $this->service->getPath() . "vo");
		$this->writeLine("\t  {");
		$this->writeLine("\t\t[Bindable]");
		$this->writeLine("\t\t[RemoteClass(alias=\"" . $this->service->getPath() . $pieces[count($pieces)-2] . "\")]");
		$this->writeLine("\t\tpublic class " . $pieces[count($pieces)-2]);
		$this->writeLine("\t\t{");
		$this->writeLine("\t\t\t public function " . $pieces[count($pieces)-2] . "(){}\n");
		
		foreach($vars as $var)
		{
			$this->writeLine("\t\t\t public var " . $var->Name . ":" . $var->DataType->Name . ";\n");
		}
		
		$this->writeLine("\t\t}");
		$this->writeLine("\t  }");	
	}
	
	protected function createVO()
	{
		$voStarted = false;
		
		foreach($this->service->Items as $method)
		{
			foreach($method->Items as $arg){
				
				if($arg->DataType->Name != "String")
				{
					if(!$voStarted){
						$folder = $this->writeStartFolder("vo");
						$voStarted = true;
					}
					
					$this->writeStartFile($arg->DataType->Name.".as");
					$this->createVOCode($arg->DataType->Items);
					$this->writeEndFile();								
				}
			}
		}
		
		if($voStarted)
			$this->writeEndFolder();
	}
	
	protected function createCode()
	{
		$this->writeText("
	  load(\"netservices.asc\");

    NetServices.setDefaultGatewayUrl(\"" . $this->getCurrentURL() . "/weborb/console\");
    var netServices = NetServices.createGatewayConnection();

    var service = netServices.getService(\"" . $this->service->Name . "\", new " . $this->service->Name . "());

    ");
		
		foreach($this->service->Items as $method)
			$this->writeText("
    service." . $method->Name . "();
    	");
			
    	$this->writeText("
    function " . $this->service->Name . "()
    {

    }
	");
    	
    	foreach($this->service->Items as $method)
     		$this->writeText("
    " . $this->service->Name . ".prototype." . $method->Name . "_Result = function(result);");
     	
	}
}
?>
