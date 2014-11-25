<?php
require_once WebOrb . 'Client/Responder.php';
require_once WebOrb . 'Config/ORBConfig.php';
require_once WebOrb . 'DCA/ReadingFormatDefinition.php';
require_once WebOrb . 'Client/WeborbClient.php';
require_once WebOrb . 'DCA/Util/MethodProperty.php';
require_once WebOrb . 'DCA/Util/DCAUtils.php';

class WebORBDataCollectionAgent
{
	const INVOCATION_COUNT = "Invocation count";
	const LAST_INVOCATION_TIME = "Last invocation time";
	const FASTEST_INVOCATION_TIME = "Fastest invocation time";
	const FAULT_CODE = "Fault count";
	const AVERAGE_INVOCATION_TIME = "Average invocation time";
	const RETURN_VALUE = "Return value";
	const METHOD_INVOCATION_RFD = "Server address|Namespace|Class name|Method name";
	
	const METHOD_INFORMATION_TYPE = "Method information";
	
	private static /*Hashtable<String, ReadingFormatDefinition>*/ $readingFormatDefinitions = array();
	private static /*int*/ $rfdCounter = 0;
	private static /*int*/ $rfiCounter = 0;
	private static /*WebORBDataCollectionAgent*/ $singleton = null;
	private static /*BusinessIntelligenceConfig*/ $bic;// = ORBConfig::getInstance()->getBusinessIntelligenceConfig();					
	public static /*ArrayList<String>*/ $monitoredRIs = array();
	
	private static /*String*/ $serverAddress = null;
	const /*String*/ id = "WebORBforPHPDataCollectionAgent";	
	private static /*boolean*/ $connected = false;
	private static /*boolean*/ $RFDChanged = true;
	
	private function __construct( /*String*/ $address )
	{
		self::$bic = ORBConfig::getInstance()->getBusinessIntelligenceConfig();
		if( $address != null )
			self::$serverAddress = $address;
	}
		
	public static function startup( /*String*/ $address )
	{
		if( self::$singleton == null )
			self::$singleton = new WebORBDataCollectionAgent( $address );
	}
	
	public static /*WebORBDataCollectionAgent*/function getInstance()
	{
		return self::$singleton;
	}
	
	public static function array_to_object($array = array()) 
	{
	    if (!empty($array)) {
	        $data = new stdClass();
	
	        foreach ($array as $akey => $aval) {
	        	if(is_array($aval))
	        	{
	        		$aval = self::array_to_object($aval);
	        	}
	            $data -> {$akey} = $aval;
	        }
	
	        return $data;
	    }
	
	    return new stdClass();;
	}
	
	public /*boolean*/function sendDCAInformationToRBI()
	{
		self::$rfdCounter = 0;
		self::$rfiCounter = 0;
		self::$readingFormatDefinitions = array();
		/*ArrayList*/ $monitoredMethods = $this->getMonitoredMethods();
		/*ReadingFormatDefinition*/ $rfd = self::createReadingFormatDefinition( self::METHOD_INVOCATION_RFD, self::METHOD_INFORMATION_TYPE );
			
		for( $i = 0; $i < count($monitoredMethods); $i++ )
		{
			/*Hashtable<String, String>*/ $readingDefinition = array();										
			$readingDefinition["Server address"] = self::$serverAddress;
			/*Method */$method = $monitoredMethods[$i];
			$readingDefinition["Namespace"] = $method->getNamespace();//$classPackage == null ? "" : $classPackage->getName();
			$readingDefinition["Class name"] = $method->getClassName();			
			$readingDefinition["Method name"] = self::getMethodName( $method->getReflactionMethod() );
			
			/*ReadingFormatInstance*/ $rfi = $rfd->createInstance( $readingDefinition );
			//rfi.createIndicator( "Method name", INVOCATION_COUNT );
			$rfi->createIndicator( "Method name", self::LAST_INVOCATION_TIME, "Number" );
			//rfi.createIndicator( "Method name", FASTEST_INVOCATION_TIME );
			//rfi.createIndicator( "Method name", FAULT_CODE );
			//rfi.createIndicator( "Method name", AVERAGE_INVOCATION_TIME );
			$rfi->createIndicator( "Method name", self::RETURN_VALUE, DCAUtils::getTypeName( $method->getReflactionClass() ) );
		}
		/*Object[]*/ $args = array();				
		/*Hashtable<Integer, String[]>*/ $rfdTable = array(); 
		/*Hashtable<Integer, HashMap<Integer, String>>*/ $rfiTable = array(); 				
		/*Hashtable<Integer, HashMap<String, String[]>>*/ $riTable = array();
		
		/*Enumeration<String>*/ $rfdKeys = array_keys(self::$readingFormatDefinitions);
		
		foreach( $rfdKeys as $key )
		{
			/*ReadingFormatDefinition*/ $readingFormatDefinition = self::$readingFormatDefinitions[$key];
			/*int*/ $rfdId = $readingFormatDefinition->getId();
			$rfdTable[$rfdId] = array( $readingFormatDefinition->getRFDString(), $readingFormatDefinition->getType());			
			$rfiTable[$rfdId] = array();
			
			/*ArrayList<ReadingFormatInstance>*/ $instances = $readingFormatDefinition->getInstances();
			foreach($instances as $readingFormatInstance)
			{
				$rfiTable[$rfdId][$readingFormatInstance->getId()] = $readingFormatInstance->getRFIString();
				/*ArrayList<ReadingIndicator>*/ $indicators = $readingFormatInstance->getIndicators();
				$riTable[$readingFormatInstance->getId()] = array();
				
				foreach ($indicators as $readingIndicator)
				{
					$riTable[$readingFormatInstance->getId()][$readingIndicator->getId()] = array($readingIndicator->getName(), "" . $readingIndicator->getDimension(), $readingIndicator->getType());													
				}				
			}
		}
		
		$args[ 0 ] = self::id;
		$args[ 1 ] = self::$serverAddress;
		$args[ 2 ] = $rfdTable;
		$args[ 3 ] = $rfiTable;
		$args[ 4 ] = $riTable;
		self::$RFDChanged = true;
//		var_dump($rfdTable);
//		var_dump($rfiTable);
//		var_dump($riTable);
//		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
		/*boolean*/ $status = self::makeRemoteInvocation( "com.tmc.management.RBIServerService", "addDCA", $args );	
		
		if( $status )
			self::$RFDChanged = false;
		var_dump($status);
		return $status;
	}
	
	public function getMonitoredRIsFromRBIServer()
	{
		/*Object[]*/ $args = array();				
		$args[ 0 ] = self::id;
		$args[ 1 ] = self::$serverAddress;
		
		self::makeRemoteInvocation( "com.tmc.management.RBIServerService", "GetMonitoredIdentificators", $args, new GetMonitoredRIsMethodResponder() );
	}
	
	public static /*int*/function getReadingFormatInstanceId()
	{
		return self::$rfiCounter++;		
	}
	
	public static /*ReadingFormatDefinition*/function createReadingFormatDefinition()
	{
		$args = func_get_args();
		if(is_array($args[0]))
		{
			return self::createReadingFormatDefinitionArray($args[0], $args[1]);
		}
		return self::createReadingFormatDefinitionString($args[0], $args[1]);
	}
	
	public static /*ReadingFormatDefinition*/function createReadingFormatDefinitionString( /*String*/ $rfd, /*String*/ $type )
	{
		if( $type == null )
			$type = "";
		
		/*ReadingFormatDefinition*/ $definition = null;
		
		if( !array_key_exists($rfd, self::$readingFormatDefinitions))
		{
			$definition = new ReadingFormatDefinition( self::$rfdCounter++, explode('|', $rfd), $type );
			self::$readingFormatDefinitions[$rfd] = $definition;
		}
		else
			$definition = self::$readingFormatDefinitions[$rfd];
		
		return $definition;
	}
	
	public static /*ReadingFormatDefinition*/function createReadingFormatDefinitionArray( /*String[]*/ $rfdTokens, /*String*/ $type )
	{
		if( count($rfdTokens) == 0 )
			return null;
		
		/*String*/ $rfd = $rfdTokens[ 0 ];
			
		$rfd = implode('|', $rfdTokens);
			
		return self::createReadingFormatDefinition( $rfd, $type );
	}		
	
	public static function reportToRBI( /*ReadingIndicator*/ $indicator )
	{
		/*Object[]*/ $args = array();
		$args[ 0 ] = self::id;
		$args[ 1 ] = self::$serverAddress;
		$args[ 2 ] = $indicator->getReportString();
//		var_dump($args);
//		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
		self::makeRemoteInvocation( "com.tmc.management.RBIServerService", "AddReport", $args );
	}
	
	private static /*String*/function getMethodName( /*Method*/ $method )
	{
		return $method->getName() . "( " . count($method->getParameters()) . " arguments )";
	}
	
	
	private static /*boolean*/function makeRemoteInvocation( /*String*/ $className, /*String*/ $methodName, /*Object[]*/ $args, $responder = null )
	{
		try
		{
			/*WeborbClient*/ $client = new WeborbClient( self::$bic->getServerConfiguration()->serverAddress );
			$client->invoke( $className, $methodName, $args, $responder );
		}
		catch( Exception $e )
		{
			Log::log( LoggingConstants::ERROR, "Error during remote call to rbi server: " . $e->getMessage() );
			
			return false;
		}
		
		return true;
	}
	
	private /*ArrayList*/function getMonitoredMethods()
	{
		/*ArrayList*/ $list = array();
		/*ArrayList*/ $nodes = self::$bic->getMonitoredClassRegistry()->getSelectedNodes(); 
		/*ArrayList*/ $nodeNames = array();
		for( $i = 0; $i < count($nodes); $i++ )
		{
			/*ServiceNode*/ $node = $nodes[$i];
			
			$nodeNames = array_merge_recursive($nodeNames, $this->getChildren( $node )); 
		}
//		var_dump($nodeNames);
		
		for( $i = 0; $i < count($nodeNames); $i++ )
		{
			/*String*/ $nodeName = $nodeNames[$i];
			
			
			try
			{								
				/*Class*/ $class = TypeLoader::loadType( $nodeName );
				
				if( $class != null )
				{
					$methods = $class->getMethods();
					foreach ($methods as $method)
					{
						if($method->isPublic())
						{							
							$method = new MethodProperty($nodeName, $method->getName());
							$list[] = $method;
						}
					}
									
					continue;
				}
			}
			catch( Exception $e )
			{
				// this is not a class
//				$e->printStackTrace();
			}
			
			try
			{	
					/*String*/ $fullMethodName = $nodeName;//substr($nodeName, 0, $braceIndex );
					/*int*/ $lastDotIndex = strrpos($fullMethodName, "." );
					/*String*/ $methodName = trim(substr($fullMethodName, $lastDotIndex+1, strlen($fullMethodName)-1));
					$method = new MethodProperty(substr($fullMethodName, 0, $lastDotIndex ), $methodName);
					$list[] = $method;
					continue;
			}
			catch( Exception $e )
			{
				
			}
			
			$methodList = DCAUtils::getPackageClasses($nodeName);
			foreach ($methodList as $method)
				$list[] = $method;	
		}
		
		return $list;
	}
	
	private /*ArrayList*/function getChildren( ServiceNode $node )
	{
		/*ArrayList*/ $children = array();

		if( count($node->Items) == 0 && strrpos($node->getFullName(),'.') != (strlen($node->getFullName())-1) && $node->getFullName() != '')
		{
			$children[] = $node->getFullName();
		}
			
		for( $i = 0; $i < count($node->Items); $i++ ){
			if ($node->Items[$i]->Selected != ServiceNode::NOT_SELECTED)		
				$children = array_merge_recursive($children, $this->getChildren( $node->Items[$i]));
		}
		return $children;
	}

	public /*void*/function update( MonitoredClass $monitoredClass ) 
	{
			/*ReadingFormatDefinition*/ $rfd = self::createReadingFormatDefinition( self::METHOD_INVOCATION_RFD, self::METHOD_INFORMATION_TYPE );			
			/*Hashtable<String, String>*/ $readingDefinition = array();													
			$readingDefinition["Server address"] =  self::$serverAddress;
			$readingDefinition["Namespace"] = $monitoredClass->getNamespace();//$classPackage == null ? "" : $classPackage->getName();
			$readingDefinition["Class name"] = $monitoredClass->getClassName();
			$readingDefinition["Method name"] = $monitoredClass->getFullMethodName();
			
			/*ReadingFormatInstance*/ $rfi = $rfd->createInstance( $readingDefinition );
			//rfi.createIndicator( "Method name", INVOCATION_COUNT );
			/*ReadingIndicator*/ $indicator = $rfi->createIndicator( "Method name", self::LAST_INVOCATION_TIME, "Number" );
			$indicator->submitValue( $monitoredClass->getInvokeTime() );
			$indicator->setTimestamp( time()*1000 );
//			var_dump($indicator->getId());
//			var_dump( self::$monitoredRIs);
//			var_dump(in_array($indicator->getId(), self::$monitoredRIs ));
			if( in_array($indicator->getId(), self::$monitoredRIs ))
				self::reportToRBI( $indicator );
			
			//rfi.createIndicator( "Method name", FASTEST_INVOCATION_TIME );
			//rfi.createIndicator( "Method name", FAULT_CODE );
			//rfi.createIndicator( "Method name", AVERAGE_INVOCATION_TIME );
			$indicator = $rfi->createIndicator( "Method name", self::RETURN_VALUE, DCAUtils::getTypeName( ));
			$indicator->submitValue( $monitoredClass->getResult() );
			$indicator->setTimestamp( time()*1000);
			if( in_array($indicator->getId(), self::$monitoredRIs))
				self::reportToRBI( $indicator );

	}	
}
	class GetMonitoredRIsMethodResponder extends Responder
	{
		
		public function errorHandler( /*Fault*/ $fault ) 
		{
			
		}

		
		public function responseHandler( /*Object*/ $adaptedObject ) 
		{
			/*Object[]*/ $ris = $adaptedObject;
			WebORBDataCollectionAgent::$monitoredRIs = array();
			if (is_array($ris))
				for( /*int*/ $i = 0; $i < count($ris); $i++ )
					WebORBDataCollectionAgent::$monitoredRIs[] = $ris[ $i ];
			else 
				WebORBDataCollectionAgent::$monitoredRIs[] = $ris;
			
		}				
	}
	


?>