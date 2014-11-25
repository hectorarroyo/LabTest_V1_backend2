<?php
require_once('ManagementService.php');
require_once('ServiceBrowser/ServiceNode.php');
require_once('ServiceBrowser/ServiceMethod.php');
require_once('ServiceBrowser/ServiceMethodArg.php');
require_once('RBIManagement/ServerConfiguration.php');
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "Log.php");
require_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "Logging" . DIRECTORY_SEPARATOR . "LoggingConstants.php");

class RBIManagementService 
{
	public /*ArrayList*/function getServices()
	{
		/*ManagementService*/ $ms = new ManagementService();
		/*ArrayList*/ $services = $ms->getServices();
		
		return $services;
	}
	
	public /*ArrayList*/function getServiceChildren( /*String*/ $servicePackage, /*String*/ $parentName )
	{
		/*ManagementService*/ $ms = new ManagementService();
		/*ArrayList*/ $services = $ms->getServiceChildren( $servicePackage, $parentName );
		/*MonitoredClassRegistry*/ $registry = ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry();
		
		for( /*int*/ $i = 0; $i < count($services); $i++ )
		{
			/*ServiceNode*/ $node = $services[$i];
			$node->Selected = $registry->isSelected( ($parentName == "")?$node->Name 
					: $parentName . "." . $node->Name );
			
			if( $node->Selected != ServiceNode::NOT_SELECTED )
				for( $j = 0; $j < count($node->Items); $j++ )
				{
					/*ServiceMethod*/ $method = $node->Items[$j];
					$method->Selected = $registry->isSelected( 
							$parentName == "" ? $node->Name . "." . $this->createMethodName( $method ) 
									: $parentName . "." . $node->Name . "." . $this->createMethodName( $method ) );
				}
		}
		
		return $services;
	}
	
	public /*void*/function selectNode( $namespaceName, $fullPath, $serviceName = null, $methodName = null )//ServiceNode $node )
	{	
		$node = null;
		/*ManagementService*/ $ms = new ManagementService();
		$serviceList = array();
		if($namespaceName != "")
			$serviceList = $ms->getServiceChildren($namespaceName, $fullPath);
		else 
			$serviceList = $ms->getServices();
		if ($serviceName != null)
		{
			foreach ($serviceList as $service)
			{
				if ($service->Name == $serviceName)
				{
					$node = $service;
					break;
				}
			}
			if ($methodName != null)
			{
				$methodList = $node->Items;
				foreach ($methodList as $method)
				{
					if ($method->Name == $methodName)
					{
						$node = $method;
						break;
					}
				}
			}
		}
		else 
		{
			if (count($serviceList)>0)
				$node = $serviceList[0]->Parent;
		}
//		var_dump($node); echo "\n";
        $parentNode = $node->Parent;
        while ($parentNode!=null)
        {
            if($parentNode->Parent != null)
            {
                $parentNode->Items = $ms->getServiceChildren($parentNode->Parent->getFullName(), $parentNode->getFullName());
            }
            
            $parentNode = $parentNode->Parent;            
        }
		$node->Selected = ServiceNode::FULLY_SELECTED;
		$node->Items = array();
		$parentNode = $node->Parent;
		while ($parentNode!=null)
		{
			$parentNode->Selected = ServiceNode::PARTLY_SELECTED;
            foreach($parentNode->Items as $key => $Item)
            {
                if($Item->getFullName() == $node->getFullName())
                {
                   $parentNode->Items[$key]->Selected = ServiceNode::FULLY_SELECTED; 
                }                 
            }
			$parentNode->Items = array($node);
			$node = $parentNode;
			$parentNode = $parentNode->Parent;
		}
		
//		/*String*/ $fullName = $node->getFullName();
//		/*ServiceNode*/ $baseNode = $node instanceof ServiceMethod ? new ServiceMethod() : new ServiceNode();
//		$baseNode->Name = $node->Name;
//		$baseNode->Selected = $node->Selected;
//		$baseNode->Parent = $node->Parent;
//		$baseNode->Items = array_merge_recursive($baseNode->Items, $node->Items );
//		
//		while( $node->Parent->Parent != null )
//			$node = $node->Parent;
//		
//		$fullName = substr($fullName, 0, strlen($node->Parent->Name) + 1);
//		
//		/*String[]*/ $fullNameParts = explode(".", $fullName);
//		$node->Parent = null;
//		/*ServiceNode*/ $tempNode = $node;
//		
//		for( $i = 1; $i < count($fullNameParts); $i++ )
//		{
//			/*ServiceNode */$child = $i == count($fullNameParts) - 1 ? $baseNode : $tempNode->findItem( $fullNameParts[ $i ] );
//			$tempNode->Items = array();
//			$tempNode->Items[] = $child;
//			$tempNode = $child;			
//		}
//		
//		$this->cleanNode( $node );
//		var_dump($node);
		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->addSelectedNode( $node );
//		Log::log(LoggingConstants::MYDEBUG,ob_get_contents());	
	}
	
	public function deselectNode( $namespaceName, $fullPath, $serviceName = null, $methodName = null )//ServiceNode $node )
	{	
		$node = null;
		/*ManagementService*/ $ms = new ManagementService();
		$serviceList = array();
		if($namespaceName != "")
			$serviceList = $ms->getServiceChildren($namespaceName, $fullPath);
		else 
			$serviceList = $ms->getServices();
		if ($serviceName != null)
		{
			foreach ($serviceList as $service)
			{
				if ($service->Name == $serviceName)
				{
					$node = $service;
					break;
				}
			}
			if ($methodName != null)
			{
				$methodList = $node->Items;
				foreach ($methodList as $method)
				{
					if ($method->Name == $methodName)
					{
						$node = $method;
						break;
					}
				}
			}
		}
		else 
		{
			if (count($serviceList)>0)
				$node = $serviceList[0]->Parent;
		}
		

		$parentNode = $node->Parent;
		while ($parentNode!=null)
		{
            if($parentNode->Parent != null)
            {
                if( !($parentNode instanceof Service) )
		            $parentNode->Items = $ms->getServiceChildren($parentNode->Parent->getFullName(), $parentNode->getFullName());
            }
			
			$parentNode = $parentNode->Parent;			
		}
		
		$node->Selected = ServiceNode::NOT_SELECTED;
//		$parentNode = $node->Parent;
		$rootNode = $node;
        $rootNode = $rootNode->Parent;
		while ($rootNode!=null)
		{
			$rootNode->Selected = ServiceNode::NOT_SELECTED;
			foreach($rootNode->Items as $key => $Item)
			{
                if($Item->getFullName() == $node->getFullName())
                {
//                    $this->makeDeep(0, 20, $rootNode->Items[$key], $node->getFullName());
                   $rootNode->Items[$key]->Selected = ServiceNode::NOT_SELECTED;
                   $rootNode->Items[$key]->Parent->Selected = ServiceNode::PARTLY_SELECTED; 
                }
				if($Item->Selected != ServiceNode::NOT_SELECTED)
				{
					$rootNode->Selected = ServiceNode::PARTLY_SELECTED;	
				}
			}
			$rootNode = $rootNode->Parent;
//			$parentNode = $parentNode->Parent;
		}
		/*ServiceNode*/ $rootNode = $node; 
		
		while( $rootNode->Parent != null )
        {
			$rootNode = $rootNode->Parent;
        }
        $tempNode = $rootNode;
        $tokens = explode(".", $node->getFullName());
		for($i=0; $i<count($tokens)-1; $i++)
        {
            $nodeKey = "";
            $tempNode->Selected = ServiceNode::NOT_SELECTED;
            foreach($tempNode->Items as $key => $Item)
            {
               if($Item->Name == $tokens[$i+1])
               {
                   $nodeKey = $key;
               } 
               if($Item->Selected != ServiceNode::NOT_SELECTED)
               {
                    $tempNode->Selected = ServiceNode::PARTLY_SELECTED;
               }               
            }
            $tempNode = $tempNode->Items[$nodeKey];
        }
         $tempNode->Selected = ServiceNode::NOT_SELECTED;
//		$this->cleanNode( $rootNode );
		/*String*/ $fullName = $node->getFullName();
//		$fullName = substr($fullName, strlen( $rootNode->Parent->Name)+1, strlen($fullName)-1);
		$rootNode->Parent = null;
		
		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->removeSelectedNode( $rootNode, $fullName );
//		$nodeToSelect = clone($node);
//		
////		/*ServiceNode*/ $rootNode = $node; 
////		
////		while( $rootNode->Parent->Parent != null )
////			$rootNode = $rootNode->Parent;
////		
////		$this->cleanNode( $rootNode );
////		/*String*/ $fullName = $node->getFullName();
////		$fullName = substr($fullName, 0, strlen( $rootNode->Parent->Name) + 1);
////		$rootNode->Parent = null;
//		
//		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->removeSelectedNode( $rootNode );//$rootNode, $fullName );
//		if ($nodeToSelect->Parent->Selected != ServiceNode::NOT_SELECTED)
//		{
//			$nodeToSelect->Parent->deleteItem($nodeToSelect->Name);
//			$parentNode = $nodeToSelect->Parent;
//			while ($parentNode!=null)
//			{
//                $Items = $parentNode->Items;
//                foreach($Items as $Item)
//                {
//                    if($Item->Selected == ServiceNode::NOT_SELECTED)
//                    {
//                        $parentNode->deleteItem($Item->Name);
//                    }
//                }
//				$nodeToSelect = $parentNode;
//				$parentNode = $parentNode->Parent;
//			}
//			ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->addSelectedNode( $nodeToSelect );
//		}
	}
    
  
	
	private function cleanNode( ServiceNode $node )
	{
//		if( $node instanceof ServiceMethod )
//		{
//			$node->Name = $this->createMethodName( $node );
//			$node->Items = array();
//		}
		
		if( $node->Selected == ServiceNode::FULLY_SELECTED || $node->Selected == ServiceNode::NOT_SELECTED )
			$node->Items = array();
		else
			for( $i = 0; $i < count($node->Items); $i++ )
			{
				/*ServiceNode*/ $child = $node->Items[$i];
				$this->cleanNode( $child );
			}
	}
	
	private /*String*/function createMethodName( ServiceMethod $method )
	{
		/*String*/ $name = $method->Name . "( ";
		
		for( $i = 0; $i < count($method->Items); $i++ )
		{
			if( $i != 0 )
				$name .= ", ";
			
			/*ServiceMethodArg*/ $arg = $method->Items[$i];
			$name .= $arg->DataType->ServerSideName;
		}
		
		$name .= " )";
		
		return $name;
	}
	
	public function saveConfiguration( /*ServerConfiguration*/ $configuration )
	{
		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->saveServerConfiguration( $configuration );
	}
	
	public /*ServerConfiguration*/function loadConfiguration()
	{
		return ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getServerConfiguration();
	}
	
	public function saveSelection()
	{
		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->saveMonitoredClassRegistry();
	}
	
	public /*ArrayList*/ function cancelSelection()
	{
		ORBConfig::getInstance()->getBusinessIntelligenceConfig()->validate();
		
		return $this->getServices();
	}
}
?>