<?php
require_once(WebOrb . "../Services//Weborb/Management/ServiceBrowser/ServiceNode.php");
require_once(WebOrb . "Registry/MonitoredClassRegistry.php");
require_once(WebOrb . "../Services/Weborb/Management/RBIManagement/ServerConfiguration.php");
require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "Util/XmlUtil.php");
require_once(WebOrb . "ORBConstants.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");


class BusinessIntelligenceConfig
{
	private /*Element*/ $configElement;
	private /*MonitoredClassRegistry*/ $registry;
	private /*ServerConfiguration*/ $configuration;
	private /*ORBConfig*/ $orbConfig;

	function __construct($root)
	{
		$this->configElement = XmlUtil::getChild($root, ORBConstants::BUSINESSINTELLIGENCE);//$root.getChild( IConfigConstants.BUSINESSINTELLIGENCE );
		$this->registry = new MonitoredClassRegistry();
		$this->configuration = new ServerConfiguration();
	}
	public function  Configure( /*Element*/ $root, ORBConfig $orbConfig )
	{
		$this->orbConfig = $orbConfig;
	    $this->configElement = XmlUtil::getChild($root, ORBConstants::BUSINESSINTELLIGENCE);//$root.getChild( IConfigConstants.BUSINESSINTELLIGENCE );

	    if( $this->configElement != null )
	    	$this->validate();
	}

	public function validate()
	{
		if( $this->configElement == null )
			return;

		/*Element*/ $monitoredServicesElement = $this->configElement->getElementsByTagName(OrbConstants::MONITOREDSERVICES);//XmlUtil::getChild($configElement, OrbConstants::MONITOREDSERVICES );

		if( $monitoredServicesElement != null )
		{
//			echo $monitoredServicesElement->item(0)->nodeName;
			/*List*/ $monitoredServices = $monitoredServicesElement->item(0)->childNodes;//getElementsByTagName(ORBConstants::MONITOREDSERVICE);

			for( $i = 0; $i < $monitoredServices->length; $i++)
		    {
		    	/*ServiceNode*/ $node = $this->parseMonitoredService( $monitoredServices->item($i), null );

//		    	if( $node != null )
//		    		$this->registry->addSelectedNode( $node );
		    }
		}
		else
			Log::log( LoggingConstants::ERROR, "Business intelligence settings are not properly configured. Can't find " . ORBConstants::MONITOREDSERVICES . " tag." );

	    /*Element*/ $rbiServerConfiguration = XmlUtil::getChild($this->configElement, OrbConstants::RBISERVERCONFIGURATION);

	    if( $rbiServerConfiguration != null )
	    {
	    	/*String*/ $serverAddress = XmlUtil::getChild($rbiServerConfiguration, "serverAddress" )->nodeValue;
	    	/*String*/ $reconnectionTimeout = XmlUtil::getChild($rbiServerConfiguration, "reconnectionTimeout" )->nodeValue;

	    	if( $serverAddress != null )
	    		$this->configuration->serverAddress = $serverAddress;

	    	if( $reconnectionTimeout != null )
	    	{
	    		$this->configuration->reconnectionTimeout = $reconnectionTimeout;
	    	}
	    }
	    else
	    	Log::log( LoggingConstants::ERROR, "Business intelligence settings are not properly configured. Can't find " . OrbConstants::RBISERVERCONFIGURATION . " tag." );
	}

	private /*ServiceNode*/function parseMonitoredService( /*Element*/ $monitoredService, /*ServiceNode*/ $parent )
	{
		try
		{
	    	/*ServiceNode*/ $node = new ServiceNode();
	    	/*Element*/ $name = XmlUtil::getChild($monitoredService, "name" );
			$node->Name = $name == null ? "" : $name->nodeValue;
	    	$node->Parent = $parent;
	    	/*Element*/ $items = XmlUtil::getChild($monitoredService, "items" );
	    	/*List*/ $children = $items == null ? array() : $items->childNodes;
	    	if (is_array($children))
	    	{
	    		$node->Items = $children;
	    	}
	    	else
	    	{
		    	for( $i = 0; $i < $children->length; $i++ )
		    	{
		    		/*ServiceNode*/ $child = $this->parseMonitoredService( $children->item( $i ), $node );

		    		if( $child != null )
		    			$node->Items[] = $child;
		    	}
	    	}

	    	/*Element*/ $selection = XmlUtil::getChild($monitoredService, "selection" );

	    	if( $selection != null && strtolower($selection->nodeValue) == "full" || $items == null )
	    		$node->Selected = ServiceNode::FULLY_SELECTED;
	    	else
	    		$node->Selected = ServiceNode::PARTLY_SELECTED;

	    	return $node;
		}
		catch( Exception $e )
		{
			Log::log( LoggingConstants::ERROR, "Can't parse monitored services for " . $parent->getFullName() . " package." );

			return null;
		}
	}

	public function saveMonitoredClassRegistry()
	{
		$ServicesElement = XmlUtil::getChild($this->configElement, OrbConstants::MONITOREDSERVICES);
		/*ArrayList*/ $selectedNodes = $this->registry->getSelectedNodes();
		if ($ServicesElement != null)
			$this->configElement->removeChild( $ServicesElement );
		$this->orbConfig->Save();
		$dom = $this->orbConfig->dom;
		/*Element*/ $monitoredServicesElement = $dom->createElement(ORBConstants::MONITOREDSERVICES );
		
		
		for( $i = 0; $i < count($selectedNodes); $i++ )
			$this->saveService( $selectedNodes[$i], $monitoredServicesElement );
		Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
		try
		{
			$this->configElement->appendChild( $monitoredServicesElement );
		}		
		catch(Exception $e)
		{
			var_dump($e->getMessage()); exit;
		}

		$this->orbConfig->Save();
	}

	public function saveServerConfiguration( ServerConfiguration $configuration )
	{
		$this->configuration = $configuration;
		/*Element*/ $serverConfigurationElement = XmlUtil::getChild($this->configElement, ORBConstants::RBISERVERCONFIGURATION);//$this->configElement->getElementsByTagName( ORBConstants::RBISERVERCONFIGURATION );
		if ($serverConfigurationElement != null)
			$this->configElement->removeChild( $serverConfigurationElement );
		
		$dom = $this->orbConfig->dom;
		
		$serverConfigurationElement = $dom->createElement(ORBConstants::RBISERVERCONFIGURATION);
		
		$serverAddressElement = $dom->createElement( "serverAddress", $configuration->serverAddress );
		$serverConfigurationElement->appendChild( $serverAddressElement );
		
		$reconnectionTimeoutElement = $dom->createElement( "reconnectionTimeout", $configuration->reconnectionTimeout );
		$serverConfigurationElement->appendChild( $reconnectionTimeoutElement );
		
		$this->configElement->appendChild($serverConfigurationElement);

		$this->orbConfig->Save();
	}

	public /*ServerConfiguration*/function getServerConfiguration()
	{
		return $this->configuration;
	}

	private /*void*/function saveService( ServiceNode $node, /*Element*/ &$parent )
	{
		$dom = $this->orbConfig->dom;
		/*Element*/ $monitoredService = $dom->createElement( ORBConstants::MONITOREDSERVICE );
		/*Element*/ $name = $dom->createElement( "name", $node->Name);
		/*Element*/ $items = $dom->createElement( "items" );
		/*Element*/ $selection = $dom->createElement( "selection", "full" );
//		$selection->textContent = "full";
//		$name->textContent = $node->Name;
		$monitoredService->appendChild($name);

		if( $node->Selected == ServiceNode::PARTLY_SELECTED )
		{
			for( $i = 0; $i < count($node->Items); $i++ )
			{
				if ($node->Items[$i]!= null)
					$this->saveService( $node->Items[$i] , $items );
			}

			$monitoredService->appendChild( $items );
		}
		else
			$monitoredService->appendChild( $selection );

		$parent->appendChild( $monitoredService );
	}

	public /*MonitoredClassRegistry*/function getMonitoredClassRegistry()
	{
		return $this->registry;
	}
}
?>