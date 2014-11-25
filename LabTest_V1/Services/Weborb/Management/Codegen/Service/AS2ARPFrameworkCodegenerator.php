<?php
/*******************************************************************
 * AS2ARPFrameworkCodegenerator.php
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

class AS2ARPFrameworkCodegenerator extends ServiceCodegenerator
{
	protected function doGenerate()
	{
		$parent = $this->service->Parent;
		
		if($parent == null)
		{
			$this->createCode();	
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
						
			foreach($allParents as $parent){
				$this->WriteStartFolder($parent->Name);
				$this->package .= $parent->Name . ".";
			}
			
			$this->createCode();											
		}						
	}

		
	protected function createVOCode($vars)
	{
		$pieces = explode('.',$this->file->Name);
		$this->writeLine("\n   class " . $this->service->getPath() . "vo." . $pieces[count($pieces)-2]);
		$this->writeLine("\t\t{");
		$this->writeLine("\t\t\t public function " . $pieces[count($pieces)-2] . "(){}\n");
		
		foreach($vars as $var)
		{
			$this->writeLine("\t\t\t public var " . $var->Name . ":" . $var->DataType->Name . ";\n");
		}
		
		$this->writeLine("\t\t}");	
	}
		
	protected function createMethodDelegateCode($method)
	{
		$args = "";
		$argsAndTypes = "";
			
		foreach($method->Items as $arg)	
		{		
			$args .= $arg->Name . ",";
			$argsAndTypes .= $arg->Name . ":".$arg->DataType->Name . ",";  
		}
		
		$this->WriteText("
	  import mx.remoting.Service;
	  import mx.remoting.PendingCall;
	  import mx.rpc.Responder;
	  import " . $this->service->getPath() ."command.*;
	      
	  import " . $this->service->getPath() ."vo.*;
	      
	  import " . $this->service->getPath() ."business.*;\n\n");		
		$this->WriteText("\t  class ".$this->service->getPath()."business.".$method->Name."Delegate
   {
      var serviceLocator:ServiceLocator;
      var responder:Responder;
      var service:mx.remoting.Service;

      function " . $method->Name . "Delegate(responder:Responder)
      {
        this.responder = responder;
        serviceLocator = ServiceLocator.getInstance();

        service = serviceLocator.getService(\"" . $this->service->getFullName() . "\");

      }

      function " . $method->Name . "(" . substr($argsAndTypes, 0, -1) . ")
      {
        var pendingCall:PendingCall = service." . $method->Name . "(" . substr($args, 0, -1) . ");
        pendingCall.responder = responder;
      }
    }	");
	}
		
	protected function createServiceLocator()
	{
		$this->WriteLine("\n\t  import mx.utils.Delegate;");
		$this->WriteLine("\t  import mx.remoting.Service;");
		$this->WriteLine("\t  import mx.remoting.debug.NetDebug;");
		$this->WriteLine("\t  import com.ariaware.arp.ServiceLocatorTemplate;");
		$this->WriteLine("\n\t  class " . $this->service->getPath() . "business.ServiceLocator extends ServiceLocatorTemplate");
		$this->WriteLine("\t  {");
		$this->WriteLine("\t\t  private static var weborbUrl:String = " . $this->getCurrentURL() . "/weborb/console\";");
		$this->WriteLine("\t\t  private static var s_instance:ServiceLocator;\n");
		$this->WriteText(" 	  private function ServiceLocator()
	       {
	        super();
	      
	        // debug
	        NetDebug.initialize();
	       }
	
	      public static function getInstance():ServiceLocator
	      {
	        if(s_instance == null)
	          s_instance = new ServiceLocator();
	
	        return s_instance;
	      }\n\n");
		$this->WriteLine("\t\t public function addServices():Void");
		$this->WriteLine("\t\t {");
		$this->WriteLine("\t\t   var service:Service = new Service(weborbUrl, null, \"".$this->service->getFullName()."\",null,null);");
		$this->WriteLine("\t\t   addService(\"" . $this->service->getFullName() . "\", service);");
		$this->WriteLine("\t\t }\n");
		$this->WriteText("\t\tpublic function getService ( serviceName:String ):mx.remoting.Service
	     {
	        // Get the service instance
	        var theService = super.getService ( serviceName );
	        //
	        // Do some additional validation that is specific to our application.
	        //
	        if ( theService instanceof mx.remoting.Service )
	        {
	          return mx.remoting.Service ( theService );
	        }
	        else
	        {
	          trace (\"Service Locator Error: Unknown service type requested - \"+serviceName);
	        }
	     }\n");
		$this->WriteLine("\t\t}");
	}
		
	protected function createCode()
	{
		$this->writeStartFolder("business");				
		$this->writeStartFile("ServiceLocator.as");
		$this->createServiceLocator();
		$this->writeEndFile();
		
		foreach($this->service->Items as $method)
		{
			$this->writeStartFile($method->Name . "delegate.as");
			$this->createMethodDelegateCode($method);
			$this->writeEndFile();		
		}
			
		$this->writeEndFolder();
		$this->writeStartFolder("control");
		$this->writeStartFile($this->service->Name . "Controller.as");
		$this->createControllerCode();
		$this->writeEndFile();
		$this->writeEndFolder();
		
		if(count($this->service->Items) > 0)
		{
			$this->writeStartFolder("command");
			
			foreach($this->service->Items as $method)
			{
				$this->writeStartFile($method->Name . "Command.as");
				$this->createMethodCommandCode($method);
				$this->writeEndFile();		
			}
				
			$this->writeEndFolder();
		}			
			
		$this->createVO();	
	}
		
	protected function createMethodCommandCode($method)
	{
		$this->WriteText("\t  import com.ariaware.arp.CommandTemplate;
   import mx.screens.Form;
   import mx.rpc.ResultEvent;
   import mx.rpc.FaultEvent;
   import " . $this->service->getPath() . "business.*;
      
   import " .$this->service->getPath()."vo.*;
        
   class " . $this->service->getPath() . "command." . $method->Name . "Command
   extends CommandTemplate
   implements mx.rpc.Responder
   {
     private var m_view:Object;

     var m_" . $method->Name . "Delegate:" . $method->Name . "Delegate;

     public function executeOperation():Void
     {
       m_" . $method->Name . "Delegate = new " . $method->Name . "Delegate ( this );

       m_" . $method->Name . "Delegate." . $method->Name . "(\n");
        $i = 0; 
         
		foreach($method->Items as $arg)
		{
			$this->WriteText("\t\t\tm_view.get" . $arg->Name . "()");
			$i += 1;
			
			if($i < count($method->Items))
				$this->WriteLine(",");
				
         }
      	$this->WriteText("
        );
      }

      public function onResult(re:ResultEvent):Void
      {
      	var returnValue:" . $method->ReturnDataType->Name . " = " . $method->ReturnDataType->Name . "(re.result);
      }

      public function onFault(fe:FaultEvent):Void
      {
        throw new Error (\"Command failed: \" + fe.fault.description);
      }
      }");	
	}
	
	protected function createControllerCode()
	{
		$this->WriteText("   import com.ariaware.arp.ControllerTemplate;  
   import " . $this->service->getPath() . "command.*;
   import " . $this->service->getPath() . "view.*;

   class " . $this->service->getPath() . "control.PhoneBookController extends ControllerTemplate
   {
     private static var s_instance:Controller;

     private function addEventListeners ()
     {
      //
      // Listen for events from the view. To separate screens may dispatch
      // the same event and these will be handled by the same event handler.
      // No two screens should use the same event for different purposes.
      //
      }\n");

      	$this->WriteText("\n\t    private function addCommands ()
      {\n");
      	
      	foreach($this->service->Items as $method)
      		$this->WriteText("        addCommand ( \"" . $method->Name . "\", " . $method->Name . "Command );\n\n"); 
      		      
      	$this->WriteText("\t      }

      public static function getInstance ( appRef )
      {
        if ( s_instance == null )
        {
          s_instance = new Controller();
          s_instance.registerApp ( appRef );
        }
        else
          return s_instance;
      }

      }");	
	}				
		
	protected function writeHeader($name)
	{			
	}
}
?>