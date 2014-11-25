<?php
/*******************************************************************
 * WDMCodegenerator.php
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
require_once(WebOrbServicesPath. "Weborb/Management/Codegen/Codegenerator.php");

class WDMCodegenerator extends Codegenerator
{
	protected $meta;

	public function setMeta(WDMMetaInspector $meta)
	{
		$this->meta = $meta;
	}

	public function getMeta()
	{
		return $this->meta;
	}

	protected function doGenerate()
	{
		$this->writeStartFolder("server");
			$this->generatePart(new WDMServerCodegenerator());
		$this->writeEndFolder();

		$this->writeStartFolder("client");
			$this->writeStartFolder("src");
				$this->generatePart(new WDMClientCodegenerator());
			$this->writeEndFolder();
		$this->writeEndFolder();
	}

	protected function writeHeader($name)
	{

	}

	protected function writeCopyright($fileName)
	{

	}

	public function generatePart(Codegenerator $childCodegenerator)
	{
		$childCodegenerator->setMeta($this->meta);
		parent::generatePart($childCodegenerator);
	}
}


?>