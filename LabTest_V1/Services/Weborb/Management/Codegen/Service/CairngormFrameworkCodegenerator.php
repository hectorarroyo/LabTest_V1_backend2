<?php
/*******************************************************************
 * CairngormFrameworkCodegenerator.php
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

class CairngormFrameworkCodegenerator extends ServiceCodegenerator
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
		
	protected function writeHeader($name)
	{ 	
	}

	protected function createCode()
	{
		$this->writeStartFolder("business");				
		$this->writeStartFile($this->service->Name . "Delegate.as");
		$this->createDelegateCode();
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
	
	protected function createDelegateCode()
	{
		$this->writeText("
	  package " . $this->service->getPath() . "business
    {
      import mx.rpc.IResponder;
      import com.adobe.cairngorm.business.ServiceLocator;
      import mx.rpc.events.FaultEvent;
      import mx.rpc.events.ResultEvent;
      import mx.rpc.AbstractOperation;
    
      import .vo.*;
    
      public class ". $this->service->Name . "Delegate
      {
        private var responder : IResponder;
        private var service : Object;

       public function ". $this->service->Name . "Delegate(responder : IResponder )
       {
          this.service = ServiceLocator.getInstance().getRemoteObject( \"". $this->service->Name . "\" );
          this.responder = responder;
        }   
        ");
		
		foreach($this->service->Items as $method)
		{	
			$args = "";
			$argsAndTypes = "";
			
			foreach($method->Items as $arg)	
			{		
				$args .= $arg->Name . ",";
				$argsAndTypes .= $arg->Name . ":".$arg->DataType->Name . ",";  
			}
				
			$this->writeText("		
		 public function ". $method->Name ."(" . substr($argsAndTypes, 0, -1) . ") : void
       {
          var call : Object = service.". $method->Name ."(" . substr($args, 0, -1) . ");        
          call.addResponder( responder );
       }\n");
        }

		$this->writeText("

      }

    } 
		");
	}
	
	protected function createMethodCommandCode($method)
	{
		$this->writeText("
	  package " . $this->service->getPath() . "command
   {
    import mx.rpc.IResponder;
    import com.adobe.cairngorm.commands.ICommand;
    import com.adobe.cairngorm.control.CairngormEvent;
    import mx.rpc.events.ResultEvent;
    import mx.rpc.events.FaultEvent;
    import mx.controls.Alert;
    import " . $this->service->getPath() . "business.*;
    
    import " . $this->service->getPath() . "vo.*;
    
    public class ". $method->Name ."Command implements ICommand, IResponder
    {

    public function execute( event : CairngormEvent) : void
    {
      var delegate : ". $this->service->Name . "Delegate = new ". $this->service->Name . "Delegate( this );
      delegate.". $method->Name ."(null);
    }

    public function result( event : Object ) : void
    {
      
        var returnValue:Object = event.result as Object;
      
    }

    public function fault( event : Object ) : void
    {
      var faultEvent : FaultEvent = FaultEvent( event );
      Alert.show( faultEvent.fault.faultString);
    }

    }

    }
		");
	}
		
	protected function createVOCode($vars)
	{
		$pieces = explode('.',$this->file->Name);
		$this->writeLine("\t  package " . $this->service->getPath() . "vo");
		$this->writeLine("\t  {");
		$this->writeLine("\t    import com.adobe.cairngorm.vo.IValueObject;");
		$this->writeLine("\t\t[RemoteClass(alias=\"" . $this->service->getPath() . $pieces[count($pieces)-2] . "\")]");
		$this->writeLine("\t\tpublic class " . $pieces[count($pieces)-2]);
		$this->writeLine("\t\t{");
		$this->writeLine("\t\t\t public function " . $pieces[count($pieces)-2] . "(){}\n");
		
		foreach($vars as $var)
		{
			$this->writeLine("\t\t\t [Bindable]");
			$this->writeLine("\t\t\t public var " . $var->Name . ":" . $var->DataType->Name . ";\n");
		}
		
		$this->writeLine("\t\t}");
		$this->writeLine("\t  }");	
//		$this->writeEndFile(); 	 
	}
}
?>
