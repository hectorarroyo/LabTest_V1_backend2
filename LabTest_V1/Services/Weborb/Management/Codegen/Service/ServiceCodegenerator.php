<?php
/*******************************************************************
 * Codegenerator.php
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

require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodegeneratorResult.php");
require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodeDirectory.php");
require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodeFile.php");
require_once(WebOrbServicesPath. "Weborb/Management/Codegen/Codegenerator.php");

abstract class ServiceCodegenerator extends Codegenerator
{
	protected $service;

	public function setService(Service $service)
	{
		$this->service = $service;
	}
	
	protected abstract function createVOCode($vars);

	protected function createVO()
	{
		$voStarted = false;
		$arrNames = array();
		foreach($this->service->Items as $method)
		{
			foreach($method->Items as $arg)
			{
				if($arg->DataType->Name != "String")
				{
					if(!$voStarted)
					{
						$folder = $this->writeStartFolder("vo");
						$voStarted = true;
					}
					if (!in_array($arg->DataType->Name, $arrNames))
					{
						$this->writeStartFile($arg->DataType->Name.".as");
						$this->createVOCode($arg->DataType->Items);
						$this->writeEndFile();

						$arrNames[] = $arg->DataType->Name;
					}
				}
			}
		}

		if($voStarted)
			$this->writeEndFolder();
	}
}
?>