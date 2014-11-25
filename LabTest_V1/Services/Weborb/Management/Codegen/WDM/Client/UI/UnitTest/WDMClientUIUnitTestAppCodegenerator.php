<?php
/*******************************************************************
 * WDMClientUIUnitTestAppCodegenerator.php
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
class WDMClientUIUnitTestAppCodegenerator extends WDMCodegenerator
{
	protected function doGenerate()
	{		
		$this->writeStartFile("UnitTests.mxml");
			
		$this->writeText("<?xml version=\"1.0\" encoding=\"utf-8\"?>
<mx:Application xmlns:mx=\"http://www.adobe.com/2006/mxml\" layout=\"absolute\" xmlns:ns1=\"weborb.utest.*\" creationComplete=\"onCreationComplete()\">
	<mx:Script><![CDATA[
		import UI.UnitTest.*;
		import weborb.utest.UnitTestEngine;

		private function onCreationComplete():void
		{
			var unitTestEngine:UnitTestEngine = new UnitTestEngine(); \n\n");
	
			foreach ($this->meta->tables as $nameTable=>$tableMeta)
			{
				$this->writeLine("\t\t\tunitTestEngine.items.addItem(new UTest".$this->meta->getClassName($nameTable)."Create());");
				$this->writeLine("\t\t\tunitTestEngine.items.addItem(new UTest".$this->meta->getClassName($nameTable)."Update());");
				$this->writeLine("\t\t\tunitTestEngine.items.addItem(new UTest".$this->meta->getClassName($nameTable)."FindLast());");
				$this->writeLine("\t\t\tunitTestEngine.items.addItem(new UTest".$this->meta->getClassName($nameTable)."FindFirst());");
				$this->writeLine("\t\t\tunitTestEngine.items.addItem(new UTest".$this->meta->getClassName($nameTable)."Delete());");
				
				$this->writeLine("");
			}
			$this->writeLine(
"\t\t\tunitTestView.engine = unitTestEngine;
	        }
	]]></mx:Script>

	<ns1:UnitTestView width=\"100%\" height=\"100%\" id=\"unitTestView\" />
</mx:Application>");

		$this->writeEndFile();		
	}
}
?>