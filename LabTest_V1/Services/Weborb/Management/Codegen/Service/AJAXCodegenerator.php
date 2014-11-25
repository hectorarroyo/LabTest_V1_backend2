<?php
/*******************************************************************
 * AJAXCodegenerator.php
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

class AJAXCodegenerator extends ServiceCodegenerator
{			
	protected function doGenerate()
	{
		$parent = $this->service->Parent;
		
		if($parent == null)
		{
			$this->writeStartFile($this->service->Name.".js");
			$this->createCode();
			$this->writeEndFile();				
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
			$this->writeStartFile($this->service->Name.".js");
			$this->createCode();
			$this->writeEndFile();		
		}
	}
	
	protected function createCode()
	{
		foreach($this->service->Items as $method)
			foreach($method->Items as $arg)
				if($arg->DataType->Name != "String")
				{
					$this->writeText("\n\t  function " . $arg->DataType->Name . "()
   {\n");
					foreach($arg->DataType->Items as $var)
					{
						$this->writeLine("\t\t this." . $var->Name . " = new Object();\t");
					}
					
					$this->writeLine("   }");
				}

		$this->writeText("\t var proxy = webORB.bind( \"" . $this->service->getFullName() . "\", \"" . $this->getCurrentURL() . "/weborb/console\" );");
		
		foreach($this->service->Items as $method)
		{	
			$args = "";
			
			foreach($method->Items as $arg)			
				$args .= $arg->Name . ",";
				
			$this->writeText("\n\t function ". $method->Name ."( " . $args . " weborbAsyncCall  )
  {
    if( weborbAsyncCall )
      proxy.". $method->Name ."( " . $args . " new Async( ". $method->Name ." ) );
    else
      return proxy.". $method->Name ."(" . substr($args, 0, -1) . ");
  }\n");
	    }
	    
	    foreach($this->service->Items as $method)
		{
			$this->writeText("\n\t function ". $method->Name ."Response( result )
  {
     alert( result );
  }\n");
		}	    
	}
	
	protected function writeHeader($name)
	{
		
	}
	
	protected function createVOCode($vars)
	{
		
	}
}
?>
