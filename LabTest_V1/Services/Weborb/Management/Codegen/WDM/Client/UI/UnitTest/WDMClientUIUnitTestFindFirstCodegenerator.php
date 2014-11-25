<?php
/*******************************************************************
 * WDMClientUIUnitTestFindFirstCodegenerator.php
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
class WDMClientUIUnitTestFindFirstCodegenerator extends WDMCodegenerator
{
	protected $tableName;
	
	public function __construct(/*string*/ $tableName)
	{
		$this->tableName = $tableName;
	}
	
	protected function doGenerate()
	{
		$className = $this->meta->getClassName($this->tableName);
		
		$this->writeStartFile("UTest".$className."FindFirst.as");
		
		$this->writeText("      package UI.UnitTest
      {
         import weborb.utest.UnitTest;
         import ".$this->meta->getClientNamespace().".*;
         import mx.rpc.Responder;

         public class UTest".$className."FindFirst extends UnitTest
         {

            public function UTest".$className."FindFirst()
            {
              super(\"".$className." - FindFirst\");
            }

            protected override function onExecute():void
            {
              ActiveRecords.".$className.".findFirst().addResponder(
              new Responder(
              function(item:".$className."):void
              {
                raiseOnResult();
              }, onFault));
            }
         }
      }
");
		$this->writeEndFile();		
	}
}
?>