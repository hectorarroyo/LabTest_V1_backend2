<?php
/*******************************************************************
 * AS2InlineCodegenerator.php
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

class AS2InlineCodegenerator extends ServiceCodegenerator
{
	protected function doGenerate()
	{
		$parent = $this->service->Parent;
		
		if($parent == null){
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
				$parent=$parent->Parent;
				$allParents[] = $parent;
			}
			
			$allParents=array_reverse($allParents);	
					
			foreach($allParents as $parent)
			{
				$this->writeStartFolder($parent->Name);
				$this->package .= $parent->Name . ".";
			}
			
			$this->serviceParents = $allParents;
			$this->writeStartFile($this->service->Name.".as");
			$this->createCode();
			$this->writeEndFile();
			$this->createVO();
		}							
	}
		
	protected function createVOCode($vars)
	{
		$pieces=explode('.',$this->file->Name);
		$this->writeLine("\t  class " . $this->service->getPath() . "vo." . $pieces[count($pieces)-2]);
		$this->writeLine("\t  {");
		$this->writeLine("\t\t public function " . $pieces[count($pieces)-2] . "(){}\n");
		
		foreach($vars as $var)
		{
			$this->writeLine("\t\t public var " . $var->Name . ":" . $var->DataType->Name . ";\n");
		}
		
		$this->writeLine("\t  }");
	}
					
	protected function createCode()
	{
		$this->writeText("
	  import mx.remoting.*;
   import mx.rpc.*;
   import mx.utils.Delegate;
	  import ".$this->service->getPath()."vo.*;\n\n");
		$this->writeLine("\t  var weborbUrl:String = \"" . $this->getCurrentURL() . "/weborb/console\";");
		$this->writeLine("\t  var service:Service;\n");			
	    $this->writeLine("\t  service = new Service(this.weborbUrl, null, \"" . $this->service->getFullName() . "\");\n");
		
	    foreach($this->service->Items as $method)
		{
			$text="\t\tfunction " . $method->Name . "( ";
			
			foreach($method->Items as $arg)
				$text .= $arg->Name . ":".$arg->DataType->Name . ",";
								
			$this->writeLine(substr($text, 0, -1) . ")");
			$this->writeLine("\t\t{");					
			$text="\t\t\tvar pendingCall:PendingCall = service." . $method->Name . "( ";
			
			foreach($method->Items as $arg)
				$text .= $arg->Name . ",";
					
			$this->writeLine(substr($text, 0, -1) . ");");	
			$this->writeLine("\t\t\tpendingCall.responder = new RelayResponder(this, \"".$method->Name."Handler\", \"OnErrorHandler\");");
			$this->writeLine("\t\t}\n");
		}
		
		foreach($this->service->Items as $method)
		{
			$this->writeLine("\t\tfunction " . $method->Name . "Handler(event:ResultEvent)");
			$this->writeLine("\t\t{");
			$this->writeLine("\t\t\tvar returnValue:" . $method->ReturnDataType->Name . " = " . $method->ReturnDataType->Name . "(event.result);");
			$this->writeLine("\t\t\ttrace( \"received result - \" + returnValue );");				
			$this->writeLine("\t\t}\n");
		}
		
		$this->writeLine("\t\tfunction OnErrorHandler( event:FaultEvent )");
		$this->writeLine("\t\t{");
		$this->writeLine("\t\t\ttrace( event.fault.faultstring );");
		$this->writeLine("\t\t}");
	}
		
	protected function writeHeader($name)
	{			
	}
}
?>