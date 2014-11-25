<?php
/*******************************************************************
 * AS3Codegenerator.php
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

class AS3Codegenerator extends ServiceCodegenerator
{
	private /*bool*/function hasVO()
	{
		foreach($this->service->Items as $method)
		{
			foreach($method->Items as $arg)
			{
				if($arg->DataType->Name != "String")
				{
					return true;				
				}
			}
		}

		return false;	
	}
	protected function doGenerate()
	{
		$parent = $this->service->Parent;

		if($parent == null)
		{
			$this->writeStartFile($this->service->Name.".as");
				$this->createCode();
			$this->writeEndFile();
			$this->writeStartFile($this->service->Name."Model.as");
				$this->createModelCode();
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
			$this->writeStartFile($this->service->Name."Model.as");
				$this->createModelCode();
			$this->writeEndFile();
			$this->createVO();
		}
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
			$this->writeLine("\t\t\t public var " . $var->Name . ":" . $var->DataType->Name . ";\n");

		$this->writeLine("\t\t}");
		$this->writeLine("\t  }");
	}

	protected function createModelCode()
	{
		$this->writeHeader($this->service->Name . "Model");
		$this->writeText("
    package ".substr($this->service->getPath(),0,-1)."
    {");
		if($this->hasVO())
			$this->writeText("
      import ".$this->service->getPath()."vo.*;			
		");
		$this->writeText("      
      [Bindable]
      public class ".$this->service->Name."Model
      {  
   ");
		$text = "";
		foreach($this->service->Items as $method)
			if($method->ReturnDataType->Name != "void")  
        		$text .= "\t\t\tpublic var ".$method->Name."Result:" . $method->ReturnDataType->Name . ";\n";
        $this->writeText($text . "
      }
    }		
		");
	}
	
	protected function createCode()
	{
		$this->writeHeader($this->service->Name);
		if(!isset($this->package)) $this->package = "";
		$this->package=substr($this->package, 0, -1);
		$this->writeLine("	  package " . $this->package);
		$this->writeText("	  {
	  import mx.rpc.remoting.RemoteObject;
	  import mx.controls.Alert;
	  import mx.rpc.events.ResultEvent;
	  import mx.rpc.events.FaultEvent;
	  import mx.rpc.AsyncToken;
       import mx.rpc.IResponder;
      
	  import ".$this->service->getPath()."vo.*;
		\n");

		$this->writeLine("	  public class " . $this->service->Name);
		$this->writeLine("	  {");
		$this->writeLine("	  private var remoteObject:RemoteObject;");
		$this->writeLine("	  private var model:".$this->service->Name."Model;");
		$this->writeLine("");
		$this->writeLine("	  public function " . $this->service->Name . "( model:" . $this->service->Name . "Model = null )");
		$this->writeLine("	  {");
		$this->writeLine("\t\t  remoteObject  = new RemoteObject(\"GenericDestination\");");
		$this->writeLine("\t\t  remoteObject.source = \"" . $this->service->getFullName() . "\";\n");

		foreach($this->service->Items as $method)
			$this->writeLine("\t\t  remoteObject." . $method->Name . ".addEventListener(\"result\"," . $method->Name . "Handler);");

		$this->writeLine("\t\t  remoteObject.addEventListener(\"fault\", onFault);");
		$this->writeLine("\t\t  ");
		$this->writeLine("\t\t   if( model == null )");
		$this->writeLine("\t\t\t  model = new " . $this->service->Name . "Model();");
		$this->writeLine("\t\t  ");
		$this->writeLine("\t\t  this.model = model;");
		$this->writeLine("\t\t  ");
		$this->writeLine("\t  }");

		$this->writeText("
      public function setCredentials( userid:String, password:String ):void
      {
        remoteObject.setCredentials( userid, password );
      }

      public function GetModel():" . $this->service->Name . "Model
      {
        return this.model;
      }");
		$this->writeLine("\t  ");
		foreach($this->service->Items as $method)
		{
			$text="\n\t  public function " . $method->Name . "( ";

			foreach($method->Items as $arg)
				$text .= $arg->Name . ":".$arg->DataType->Name . ",";

			$this->writeLine($text." responder:IResponder = null):void");
			$this->writeLine("\t {");
			$text="\t\t  var asyncToken:AsyncToken = remoteObject." . $method->Name . "( ";

			foreach($method->Items as $arg)
				$text .= $arg->Name . ",";

			$this->writeLine(substr($text, 0, -1) . ");");
			$this->writeLine("\t  
			if( responder != null )
				asyncToken.addResponder( responder );
		}");
		}

		foreach($this->service->Items as $method)
		{
			$this->writeLine("\t  public virtual function " . $method->Name . "Handler(event:ResultEvent):void ");
			$this->writeLine("\t  {");
			$this->writeLine("\t\t  var returnValue:" . $method->ReturnDataType->Name . " = event.result as " . $method->ReturnDataType->Name . ";");
			$this->writeLine("\t\t  model." . $method->Name . " = event.result as " . $method->ReturnDataType->Name . ";");
			$this->writeLine("\t  }\n");
		}

		$this->writeText("\t public function onFault (event:FaultEvent):void
  	{
  		Alert.show(event.fault.faultString, \"Error\");
  	}
  }

  }");

	}

	protected function writeHeader($name)
	{
		$include = $this->service->getFullName();
		$text = "
	  /***********************************************************************
	  The generated code provides a simplified mechanism for invoking methods
	  on the Weborb.Examples.CarRental.CarRentalService class via WebORB.
	  You can add the code to your Flex Builder project and use the
	  class as shown below:
	  	  import $include;
		  var serviceProxy:$name = new $name();
	  // make sure to substitute foo() with a method from the class
	  serviceProxy.foo();
	  The generated code does not provide any handling of the result values.
	  We recommend the following approach to integrate this class into your
	  application:

	  (If using Model-View-Controller)
	  - Modify the constructor of the class below to accept the controller object
	  - Modify response handlers to pass return values to the controller

	  (if not using MVC)
	  - Modify the constructor of the class below to accept your View object
	  - Modify response handlers to display the result directly in the View
	  ************************************************************************/\n";
		$this->writeText($text);
	}

	protected function getInfo()
	{
		return	"<b>What has just happened?</b> You selected a class deployed in WebORB and the console produced a corresponding client-side code to invoke methods on the selected class.
<b>What can the generated code do?</b> The generated code accomplishes several goals:<ul>
<li>Generates ActionScript v3 value object classes for all complex types used in the remote PHP class.</li><li>Generates RemoteObject declaration and handler functions for each corresponding remote method</li><li>Generates a utility wrapper class making it easier to perform remoting calls</li>
</ul><b>What can I do with this code?</b> You can download the code, add it to your Flex Builder (or Flex SDK) project and start invoking your PHP methods. The code is the basic minimum one would need to perform a remote invocation. It includes all the stubs for each remote method. Make sure to add your application logic to the handler functions.
<b>How can I download the code?</b> Unfortunately, at this point you need to copy and paste the generated code into your Flex Builder project. We are working to make it easier in a future release";

	}
}
?>
