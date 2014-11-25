<?php
/*******************************************************************
 * AS3ARPFrameworkCodegenerator.php
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

class AS3ARPFrameworkCodegenerator extends ServiceCodegenerator
{
	protected function doGenerate()
	{
		$parent = $this->service->Parent;
		
		if($parent == null)
			$this->createCode();	
		else
		{
			$allParents = array();
			$allParents[] = $parent;
			
			while($parent->Parent != null){					
				$parent = $parent->Parent;
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
		$this->writeLine("\n\t  package " . $this->service->getPath() . "vo");
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
		
	protected function createServiceLocatorCode(){
		$this->WriteText("   package " . $this->service->getPath() . "business
   {
    import org.osflash.arp.ServiceLocatorTemplate;
    import org.osflash.arp.AMF0Service;

    public class ServiceLocator extends ServiceLocatorTemplate
    {
      private static var weborbUrl:String = \"" . $this->getCurrentURL() . "/weborb/console\";
      private static var s_instance:ServiceLocator;

      function ServiceLocator()
      {
        super();
      }

      public static function getInstance():ServiceLocator
      {
        if(s_instance == null)
          s_instance = new ServiceLocator();

        return s_instance;
      }

      override protected function addServices():void
      {
        var service:AMF0Service = new AMF0Service(weborbUrl, \"" . $this->service->getPath() . $this->service->Name . "\",null);
        addService(\"". $this->service->getPath() . $this->service->Name . "\", service);
      }

    }
   }");
	}
		
	protected function createCode()
	{
		$this->writeStartFolder("business");				
		$this->writeStartFile("ServiceLocator.as");
		$this->createServiceLocatorCode();
		$this->writeEndFile();				
		$this->writeEndFolder();
		
		$this->writeStartFolder("control");
		$this->writeStartFile($this->service->Name . "Controller.as");
		$this->createControllerCode();
		$this->writeEndFile();
		$this->writeEndFolder();
		
		if(count($this->service->Items) > 0)
		{
			$this->writeStartFolder("command");
			
			foreach($this->service->Items as $method){
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
		$args = "";
			
		foreach($method->Items as $arg)	
			$args .= "viewRef.get" . $arg->Name . "() ,";  
		
		$this->writeText("
	  package " . $this->service->getPath() . "command
   {
      
    import mx.rpc.events.ResultEvent;
    import mx.rpc.events.FaultEvent;
    import org.osflash.arp.AMF0Service;
    import org.osflash.arp.AMF0PendingCall;
    import org.osflash.arp.AMF0RelayResponder;

    import " . $this->service->getPath() . "business.*;
      
    import " . $this->service->getPath() . "vo.*;
      
    import flash.utils.describeType;

    public class " . $method->Name . "Command
    {
        private var viewRef:*;
        
      public function execute ( viewRef:* ):void
      {
        trace (\"" . $method->Name . "Command.execute()\");

        this.viewRef = viewRef;
        var service:AMF0Service = ServiceLocator.getInstance().getService ( \"" . $this->service->getFullName() . "\");
        var pendingCall:AMF0PendingCall = service." . $method->Name . "(" . substr($args, 0, -1) . ");

        pendingCall.responder = new AMF0RelayResponder(this, \"onResult\", \"onFault\");
      }

      public function onResult(re:ResultEvent):void
      {
      
        var returnValue:Object = re.result as Object;
      
      }

      public function onFault(fe:FaultEvent):void
      {
        trace (\"failed: \" + fe.fault.message);
      }
      }
      }
		");
		
	}
		
	protected function createControllerCode()
	{
		$this->writeText("
	  package ". $this->service->getPath() ."control
   {
      import org.osflash.arp.ControllerTemplate;
      import org.osflash.arp.*;
      import " . $this->service->getPath() . "command.*;
      import " . $this->service->getPath() . "view.*;

      public class " . $this->service->Name . "Controller extends ControllerTemplate
      {
      private static var s_instance:Controller;

      private function addEventListeners ():void
      {
      //
      // Listen for events from the view. To separate screens may dispatch
      // the same event and these will be handled by the same event handler.
      // No two screens should use the same event for different purposes.
      //
      }

      private function addCommands ():void
      {
      ");
		
		foreach($this->service->Items as $method)
		{
			$this->writeLine("  addCommand ( \"" . $method->Name . "\", " . $method->Name . "Command );\n");
			$this->writeText("\t\t");        
		}
		
        $this->writeText(" }

      public static function getInstance ( appRef:* = null ):" . $this->service->Name . "Controller
      {
        if ( s_instance == null )
        {
        s_instance = new " . $this->service->Name . "Controller();

        if(appRef != undefined)
        s_instance.registerApp ( appRef );
      }
        else
        return s_instance;
      }

    }
	  }
		");
	}
		
	protected function writeHeader($name)
	{			
	}
}
?>