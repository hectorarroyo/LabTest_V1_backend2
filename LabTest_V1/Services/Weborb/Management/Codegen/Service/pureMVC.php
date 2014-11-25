<?php

require_once("ServiceCodegenerator.php");

class pureMVC extends ServiceCodegenerator
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
			$this->createCode();
				
		}
	}
	
	protected function createCode()
	{
		$this->writeStartFolder("controller");
						
			if(count($this->service->Items) > 0)
			{
				foreach($this->service->Items as $method){
					$this->writeStartFile($method->Name . "Command.as");
						$this->createControllerCode($method);
					$this->writeEndFile();		
				}				
			}
			$this->writeStartFile("StartupCommand.as");
				$this->creatStartupCommandCode();
			$this->writeEndFile();
		$this->writeEndFolder();
		$this->writeStartFolder("view");
			$this->writeStartFolder("components");
				$this->writeStartFile("README.txt");
					$this->writeText("
			Place all UI components in the view/components folder. Each component should have a corresponding mediator.
            Mediators should be placed in the view folder.");
				$this->writeEndFile();
			$this->writeEndFolder();
			$this->writeStartFile($this->service->Name . "Mediator.as");
				$this->createMediatorCode();
			$this->writeEndFile();
		$this->writeEndFolder();
		$this->writeStartFolder("model");
			$this->writeStartFolder("enum");
				$this->writeStartFile("README.txt");
					$this->writeText("
            Place any applicable enumeration definitions in the model/enum folder					
					");
				$this->writeEndFile();
			$this->writeEndFolder();
			$this->createOwenVO();
			$this->writeStartFile($this->service->Name."Proxy.as");
				$this->createProxyCode();
			$this->writeEndFile();
		$this->writeEndFolder();
		$this->writeStartFile($this->service->Name."Facade.as");
			$this->createFacadeCode();
		$this->writeEndFile();
	}
	
	private function createFacadeCode()
	{
		$this->writeText("
    package ".$this->service->getPath()."
    {
      import org.puremvc.interfaces.IFacade;
      import org.puremvc.patterns.facade.Facade;
      import ".$this->service->getPath()."controller.*;
      import ".$this->service->getPath()."model.vo.*;     
      import ".$this->service->getPath()."*;
        
      public class ".$this->service->Name."Facade extends Facade implements IFacade
      {
          public static const STARTUP:String = \"startup\";
          public static const ERROR:String = \"error\"; 
          ");
		foreach($this->service->Items as $method)
		{
			$this->writeText("
          public static const ".$method->Name.":String = \"".$method->Name."\";
          public static const ".$method->Name."_finished:String = \"".$method->Name." finished\";
          ");
		}  
		$this->writeText("
          [Bindable]
          public var ".$this->service->Name."Result:".$this->service->Name."ResultVO;
      
          public function ".$this->service->Name."Facade() 
          {
            ".$this->service->Name."Result = new ".$this->service->Name."ResultVO();
          }
          
          public static function getInstance() : ".$this->service->Name."Facade 
          {
            if ( instance == null ) instance = new ".$this->service->Name."Facade( );
              return instance as ".$this->service->Name."Facade;
          }   

          override protected function initializeController( ) : void 
          {
            super.initializeController();   
            
            registerCommand( STARTUP, ".$this->service->getPath()."controller.StartupCommand );
            ");
		foreach ($this->service->Items as $method)
			$this->writeText("registerCommand( ".$method->Name.", ".$this->service->getPath().".controller.".$method->Name."Command );
		  ");
		$this->writeText("                  
          }
      }
    }		
		");
	}
	
	private function createProxyCode()
	{
		$this->writeText("
    package ".$this->service->getPath()."model
    {    
      import org.puremvc.interfaces.IProxy;
      import org.puremvc.patterns.proxy.Proxy;
      import org.puremvc.patterns.observer.Notification;
      import ".$this->service->getPath().".model.vo.*;
      import ".$this->service->getPath().".*;          
      import mx.rpc.remoting.RemoteObject;
      import mx.rpc.events.ResultEvent;
      import mx.rpc.events.FaultEvent;
      import mx.rpc.AsyncToken;  
        
      public class ".$this->service->Name."Proxy extends Proxy implements IProxy
      {
        public static const NAME:String = '".$this->service->Name."Proxy';
        private var remoteObject:RemoteObject;
        
        public function ".$this->service->Name."Proxy( )
        {
          super( NAME );
          remoteObject  = new RemoteObject(\"GenericDestination\");
          remoteObject.source = \"".$this->service->getPath().$this->service->Name."\";");
		foreach($this->service->Items as $method)
		{
          $this->writeText("
          remoteObject.".$method->Name.".addEventListener(\"result\",".$method->Name."Handler);");
		}
		$this->writeText("
          remoteObject.addEventListener(\"fault\", onFault);        
        }");
		foreach($this->service->Items as $method)       
		{
			$this->writeText("
        public function ".$method->Name."(");// <xsl:for-each select="arg">
			/*int*/$i = 0;
			foreach($method->Items as $arg)
			{
				$this->writeText($arg->Name.":".$arg->DataType->Name);
				if(count($method->Items) != $i+1) $this->writeText(", ");
				$i++;
			}
			$this->writeText("):void        
        {
          remoteObject.".$method->Name."(");
			/*int*/$i = 0;
			foreach($method->Items as $arg)
			{
				$this->writeText($arg->Name);
				if(count($method->Items) != $i+1) $this->writeText(", ");
				$i++;
			}
			$this->writeText(")        
        }
        
        public virtual function ".$method->Name."Handler(event:ResultEvent):void
        {                   
            sendNotification( ".$this->service->Name."Facade.".$method->Name."_finished, event.result );
        }");
		}
		$this->writeText("     
        public function onFault (event:FaultEvent):void
        {
            sendNotification( ".$this->service->Name."Facade.ERROR, event.fault.faultString );
        }       
      }
    }		
		");
	}
	
	private  function createOwenVO()
	{
		$this->writeStartFolder("vo");
			$arrNames = array();
			foreach($this->service->Items as $method)
			{
				foreach($method->Items as $arg)
				{
					if($arg->DataType->Name != "String")
					{
						if (!in_array($arg->DataType->Name, $arrNames))
						{
							$this->writeStartFile($arg->DataType->Name.".as");
							$this->createVOCode($arg->DataType);
							$this->writeEndFile();
	
							$arrNames[] = $arg->DataType->Name;
						}
					}
				}
			}
			$this->writeStartFile($this->service->Name."ResultVO.as");
				$this->createResultVOCode(count($arrNames));
			$this->writeEndFile();
		$this->writeEndFolder();
	}
	
	private function createResultVOCode(/*int*/ $countVO)
	{
		$this->writeText("
    package ".$this->service->getPath()."model.vo
    {");
		if($countVO>0) $this->writeText("
      import ".$this->service->getPath()."model.vo.*;");
		$this->writeText("
      [Bindable]
      public class ".$this->service->Name."ResultVO
      {\n");
		foreach ($this->service->Items as $method)
			if($method->ReturnDataType->Name != "void")
				$this->writeText("\n\t\t\tpublic var ".$method->Name."Result:".$method->ReturnDataType->Name.";");
		$this->writeText("
      }
    }		
		");
	}
	
	private function createMediatorCode()
	{
		 $this->writeText("
    package ".$this->service->getPath()."view
    {
      import org.puremvc.interfaces.IMediator;
      import org.puremvc.interfaces.INotification;
      import org.puremvc.patterns.mediator.Mediator;
      import org.puremvc.patterns.observer.Notification;
      import ".$this->service->getPath()."*;
      import ".$this->service->getPath()."model.*;
      import mx.controls.Alert;   

      public class ".$this->service->Name."Mediator extends Mediator implements IMediator
      {
        private var proxy:".$this->service->Name."Proxy;      
        public static const NAME:String = '".$this->service->Name."Mediator';

        public function ".$this->service->Name."Mediator( viewComponent:Object = null )
        {
            super( viewComponent );         
            proxy = facade.retrieveProxy( ".$this->service->Name."Proxy.NAME ) as ".$this->service->Name."Proxy;            
        }
        
        override public function getMediatorName():String
        {
          return NAME;
        }
        
        override public function listNotificationInterests():Array
        {
          return [
                    ".$this->service->Name."Facade.ERROR,\n");
		 /*int*/$i = 0;
		 foreach($this->service->Items as $method)
		 {
		 	$this->writeText("\t\t\t\t".$this->service->Name."Facade.".$method->Name."_finished");
		 	if( $i+1 != count($this->service->Items))
		 		$this->writeText(",\n");
		 	$i++;
		 }
		 $this->writeText("
          ];
        }
        
        override public function handleNotification( note:INotification ):void
        {
          switch ( note.getName() )
          {
            case ".$this->service->Name."Facade.ERROR:
              Alert.show(note.getBody() as String, \"Error\");
              break;");
		 foreach($this->service->Items as $method)
		 {
		 	$this->writeText("
		  case ".$method->Name."Facade.".$this->service->Name."_finished:");
		 	if($method->ReturnDataType->Name != "void")
		 	{
		 		$this->writeText("
              ".$this->service->Name."Facade.getInstance().".$this->service->Name."Result.".$method->Name."Result = note.getBody() as ".$method->ReturnDataType->Name.";");
		 	}
		 	$this->writeText("
              break;");
		 }
		 $this->writeText("	                          
          }
        }           
      }

    } 		 
		 ");
	}
	
	private function creatStartupCommandCode()
	{
		$this->writeText("
package ".$this->service->getPath()."controller
{
      import org.puremvc.interfaces.ICommand;      
      import org.puremvc.interfaces.INotification;      
      import org.puremvc.patterns.command.SimpleCommand;
      import ".$this->service->getPath().".model.*;
      import ".$this->service->getPath().".view.*;

      public class StartupCommand extends SimpleCommand implements ICommand
      {
        override public function execute( notification:INotification ) : void   
        {       
          facade.registerProxy( new ".$this->service->Name."Proxy() );    
          facade.registerMediator( new ".$this->service->Name."Mediator() );
        }
      }

}    ");
	}
	
	private function createControllerCode($method)
	{
		$this->writeText("
package ".$this->service->getPath()."controller
{
      import org.puremvc.interfaces.ICommand;      
      import org.puremvc.interfaces.INotification;      
      import org.puremvc.patterns.command.SimpleCommand;
      import ".$this->service->getPath()."model.*;

      public class ".$method->Name."Command extends SimpleCommand implements ICommand
      {
        override public function execute( notification:INotification ) : void   
        {
          var args:Array = notification.getBody() as Array;
          var proxy:".$this->service->Name."Proxy = facade.retrieveProxy( ".$this->service->Name."Proxy.NAME ) as ".$this->service->Name."Proxy;");
		$this->writeLine("");
		foreach($method->Items as $arg)
		{
			$this->writeLine("\t\tvar ".$arg->Name.":".$arg->DataType->Name.";");
		}
        $this->writeLine("");
        $this->writeText("   
          if( args != null )
          {
          	args.reverse();");
		$this->writeLine("");
		foreach($method->Items as $arg)
		{
			$this->writeLine("\t\t\t".$arg->Name." = ".$arg->DataType->Name."(args.pop());");
		}
        $this->writeText("
          }
          
          proxy.".$method->Name."(");
        $i = 0;
		foreach($method->Items as $arg)
		{
			$this->writeText($arg->Name);
			if ( $i+1 != count($method->Items))
				$this->writeText(",");
			$i++;
		}  
        $this->writeText(");"); 
        $this->writeLine("");
        $this->writeText("
        }
      }

}
		");
		
	}
	
	protected function writeHeader($name)
	{
			
	}
	
	protected function createVOCode($vars)
	{
		$this->writeText("
    package ".$this->service->getPath()."model.vo
    {     
      [RemoteClass(alias=\"".$this->service->getPath().$vars->Name."\")]
      public class ".$vars->Name."
      {");
		foreach ($vars->Items as $field)
			$this->writeText("
          public var ".$field->Name.":".$field->DataType->Name.";			
			");
		$this->writeText("       
          public function ".$vars->Name."( ");
		/*int*/$i = 0;
		foreach ($vars->Items as $field)
		{
			$this->writeText($field->Name.":".$field->DataType->Name." = null");
			if(count($vars->Items) != $i+1)
				$this->writeText(", ");
			$i++;
		}
		$this->writeText(")
          {\n");
		foreach ($vars->Items as $field)
			$this->writeText("\t\t\tthis.".$field->Name." = ".$field->Name.";\n");
          $this->writeText("
          }
      }
    }		
		");		
	}
}

?>
