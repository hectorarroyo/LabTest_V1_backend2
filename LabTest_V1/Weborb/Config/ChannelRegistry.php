<?php
require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "Util/XmlUtil.php");
require_once(WebOrb . "V3Types/core/Channel.php");

class ChannelRegistry
{
	private /*ArrayList*/ $serviceChannels; 
	private static /*ChannelRegistry*/ $singleton;
	const CHANNELS = "channels";
	const CHANNEL_DEFINITION = "channel-definition";
	const ENDPOINT = "endpoint";
	const POLLING_ENABLED = "polling-enabled";
	const POLLING_INTERVAL_SECONDS = "polling-interval-seconds";
	
	public function __construct()
	{
		$serviceChannels = array();
	}
	
	public static /*ChannelRegistry*/function getInstance()
	{
		if( self::$singleton == null )
		{
			self::$singleton = new ChannelRegistry();
			self::$singleton->configure( ORBConfig::getInstance()->getFlexConfigPath() );
		}
		
		return self::$singleton;
	}
	
	public /*String*/function getConfigFileName()
    {
        return "services-config.xml";
    }	
	
	public function configure( /*String*/ $basePath )
	{
		/*Document*/ $configDoc = new DomDocument();;
        
//        try
//        {
//	         SAXBuilder builder = new SAXBuilder();
//       	 
//       	 byte[] configBytes = ClassLoaders.loadResourceBytes( basePath + File.separator + getConfigFileName() );
//       	 configDoc = builder.build( new ByteArrayInputStream( configBytes ));
//        }
//        catch( Throwable exception )
//        {
//            if( Log.isLogging( LoggingConstants.ERROR ) )
//                Log.log( LoggingConstants.ERROR, "Unable to parse " + getConfigFileName(), exception );
//
//            return;
//        }
		$configDoc->load($basePath . "/" . $this->getConfigFileName());
        /*Element*/ $root = $configDoc->documentElement;
        
        /*List*/ $channelDefinitions = XmlUtil::getChild($root, self::CHANNELS)->getElementsByTagName( self::CHANNEL_DEFINITION );
        
        for( $i = 0; $i < $channelDefinitions->length; $i++ )
        {
        	/*Element*/ $channelDefinition = /*(Element)*/$channelDefinitions->item( $i );
        	/*Element*/ $endpointElement = XmlUtil::getChild($channelDefinition, self::ENDPOINT );
        	/*String*/ $id = $channelDefinition->getAttribute( "id" );
        	/*String*/ $endpoint = $endpointElement->getAttribute( "uri" );
        	/*Channel*/ $channel = new Channel( $id, $endpoint );
        	$channel->setChannelClass( $channelDefinition->getAttribute( "class" ) );
        	$channel->setEndpointClass( $endpointElement->getAttribute( "class" ) );
        	$channel->setProperties( $this->parseProperties( XmlUtil::getChild($channelDefinition, "properties" ) ) );
        	
        	$this->serviceChannels[] = $channel;
        }
        
	} 
	
	public /*ArrayList*/function getChannels()
	{
		return $this->serviceChannels;
	}
	
	private /*Hashtable*/function parseProperties( /*Element*/ $propertiesElement )
     {
     	 /*Hashtable*/ $props = array();
     	 /*List*/ $propsNodes = $propertiesElement->childNodes;
		 for($i = 0;$i<$propsNodes->length; $i++)
		 {
		 	/*Object*/ $xmlNode = $propsNodes->item($i);

		 	if (@$xmlNode->childNodes->length > 1)
		 	{
		 		$props[$xmlNode->nodeName] = $this->parseProperties($xmlNode);
		 	}
		 	else
		 	{
		 		if(trim($xmlNode->textContent) == null) continue;
		 		$props[$xmlNode->nodeName] = $xmlNode->textContent;
		 	}

		 }

		 return $props;
    }
	
//	private Hashtable parseProperties( Element propertiesElement )
//    {
//        Hashtable props = new Hashtable();
//        
//        if( propertiesElement == null )
//        	return props;
//        
//        List propsNodes = propertiesElement.getChildren();
//
//        for( int i = 0; i < propsNodes.size(); i++ )
//        {
//       	 Object xmlNode = propsNodes.get( i );
//       	 
//            if( !(xmlNode instanceof Element) )
//                continue;
//
//            Element xmlProperty = (Element)xmlNode;
//
//            if( xmlProperty.getContent().size() == 1 && xmlProperty.getContent().get( 0 ) instanceof Text )
//                props.put( xmlProperty.getName(), ( (Text)( xmlProperty.getContent( ).get( 0 ) ) ).getValue() );
//            else if( xmlProperty.getContent().get( 0 ) instanceof String )
//           	 props.put( xmlProperty.getName(), (String)( xmlProperty.getContent( ).get( 0 ) ) );
//            else
//                props.put( xmlProperty.getName(), parseProperties( xmlProperty ) );
//        }
//
//        return props;
//    }
}
?>