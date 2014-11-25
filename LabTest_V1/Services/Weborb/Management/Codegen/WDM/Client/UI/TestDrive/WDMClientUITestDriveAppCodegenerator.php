<?php
/*******************************************************************
 * WDMClientUITestDriveAppCodegenerator.php
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
class WDMClientUITestDriveAppCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{
		$this->writeStartFile("testdrive.mxml");
		$this->writeText("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<mx:Application xmlns:mx=\"http://www.adobe.com/2006/mxml\" layout=\"absolute\" xmlns:ui=\"UI.TestDrive.*\" backgroundGradientColors=\"[#ffffff, #ffffff]\">
	<ui:".$this->meta->getClassName($this->meta->database->DatabaseName)."DbView width=\"100%\" height=\"100%\" top=\"20\" bottom=\"20\" left=\"20\" right=\"20\" />
</mx:Application>");

		$this->writeEndFile();		
	}
}
?>