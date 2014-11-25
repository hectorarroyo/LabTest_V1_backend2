<?php
require_once(WebOrb . "Config/ORBConfig.php");
require_once("ServiceDestination.php");
require_once(WebOrb . "Util/XmlUtil.php");
require_once(WebOrb . "Config/FlexRemotingServiceConfig.php");

class DestinationsConfigurator 
{
    public /*Element*/ $xmlNode;
    private static /*DestinationsConfigurator*/ $singleton;

   	public static /*DestinationsConfigurator*/function getInstance()
  	{
    	if( self::$singleton == null )
    		self::$singleton = new DestinationsConfigurator();
    		
    	return self::$singleton;
  	}
    
    public /*ArrayList*/function getDestinations()
    {
    	/*ArrayList*/ $serviceDestinations = array();    	
    	/*Element*/ $root = FlexRemotingServiceConfig::getConfigDoc()->documentElement;
    	/*List*/ $destinationNodes = $root->getElementsByTagName( "destination" );
    	
    	for( $i = 0; $i < $destinationNodes->length; $i++ )
        {
       	 	/*Element*/ $destElement = /*(Element)*/$destinationNodes->item( $i );
            /*String*/ $destinationId = $destElement->getAttribute( "id" );           
            /*String*/ $source = XmlUtil::getChild(XmlUtil::getChild($destElement, "properties" ), "source" )->textContent;
            /*ServiceDestination*/ $destination = new ServiceDestination( $destinationId, $source );
            
            /*Element*/ $channelsElement = XmlUtil::getChild($destElement, "channels" );
            
            if( $channelsElement != null && XmlUtil::getChild($channelsElement, "channel" ) != null )
            {
            	/*String*/ $channel = XmlUtil::getChild($channelsElement, "channel" )->getAttribute( "ref" );
            	
            	if( $channel != "" )
            		$destination->Channel = $channel;            		
            }
            
            /*Element*/ $securityElement = XmlUtil::getChild($destElement, "security" );
            /*Element*/ $rolesElement = null;
            /*Element*/ $constraintElement = null;
            
            if( $securityElement != null )
            	$constraintElement = XmlUtil::getChild($securityElement, "security-constraint" );
            
            if( $constraintElement != null )
            	$rolesElement = XmlUtil::getChild($constraintElement, "roles" );
            
            if( $rolesElement != null )
            {
            	/*List*/ $roles = $rolesElement->getElementsByTagName( "role" );
            	
            	for( $j = 0; $j < $roles->length; $j++ )
            	{
            		/*Element*/ $role = $roles->item( $j );            		
            		array_push($destination->Roles, $role->textContent );
            	}
            }
            
            array_push($serviceDestinations,$destination );
        }
//        var_dump($serviceDestinations);exit;
        return $serviceDestinations;
    }

    public /*ServiceDestination*/function create( ServiceDestination $serviceDestination )
    {
        $this->validate( $serviceDestination );
        $this->checkExistance( $serviceDestination );
		
        /*Element*/ $dom = FlexRemotingServiceConfig::getConfigDoc();
        
        /*Element*/ $serviceDestinationNode = $dom->createElement( "destination" );//new Element( "destination" );
        /*Element*/ $properties = $dom->createElement( "properties" );//new Element( "properties" );
        /*Element*/ $source = $dom->createElement( "source", $serviceDestination->ServiceId);//new Element( "source" );
        /*Element*/ $security = $dom->createElement( "security" );//new Element( "security" );
        /*Element*/ $securityConstraint = $dom->createElement( "security-constraint" );//new Element( "security-constraint" );
        /*Element*/ $roles = $dom->createElement( "roles" );//new Element( "roles" );
        /*Element*/ $channels = $dom->createElement( "channels" );//new Element( "channels" );
		

        
        if( !$serviceDestination->Channel == "default channel" )
        {
        	$channels->appendChild($dom->createElement("channel")->setAttribute( "ref", $serviceDestination->Channel));
        	//channels.addContent( new Element( "channel" ).setAttribute( "ref", serviceDestination.Channel ) );
        	$serviceDestinationNode->appendChild( $channels );
        }
        
        for( $i = 0; $i < count($serviceDestination->Roles); $i++ )
        	$roles->appendChild($dom->createElement("role", $serviceDestination->Roles[$i])); //new Element( "role" ).setText( (String)serviceDestination.Roles.get( i ) ) );
        
        $serviceDestinationNode->setAttribute( "id", $serviceDestination->DestinationId );

        $properties->appendChild( $source );
        $serviceDestinationNode->appendChild( $properties );
        
        if( count($serviceDestination->Roles) > 0 )
        {
        	$securityConstraint->appendChild( $roles );
        	$securityConstraint->appendChild($dom->createElement( "auth-method","Custom"));// new Element( "auth-method" ).setText( "Custom" ) );
        	$security->appendChild( $securityConstraint );
        	$serviceDestinationNode->appendChild( $security );
        }
		
		$root = $dom->documentElement;
		$root->appendChild($serviceDestinationNode);

		$dom->save(OrbConfig::getInstance()->getFlexConfigPath() . FlexRemotingServiceConfig::REMOTINGSERVICE_FILE);
        /*IDestination*/ $dest = new FlexRemotingServiceConfig();
		$dest->processDestination( ORBConfig::getInstance(), $serviceDestination->DestinationId, $serviceDestinationNode );
        ORBConfig::getInstance()->getDataServices()->getDestinationManager()->addDestination( $serviceDestination->DestinationId, $dest );
        return $serviceDestination; 
    }

    public function update( ServiceDestination $serviceDestination, ServiceDestination $newServiceDestination )
    {
    	$dom = FlexRemotingServiceConfig::getConfigDoc();
        if ( $this->isReadOnly( $serviceDestination ) )
            throw new Exception( "Destination can't be updated" );

        if ( !$serviceDestination->DestinationId == $newServiceDestination->DestinationId )
            $this->checkExistance($newServiceDestination);

        $this->validate($newServiceDestination);
		
        /*Element*/ $root = $dom->documentElement;
        /*List*/ $children = $root->getElementsByTagName( "destination" );
    	/*Element*/ $serviceDestinationNode = null;
    	
    	for( $i = 0; $i < $children->length; $i++ )
    	{
    		$serviceDestinationNode = $children->item( $i );
    		if( $serviceDestinationNode->getAttribute( "id" ) == $serviceDestination->DestinationId )
    			break;
    	}
        
        if ( $serviceDestinationNode == null )
            throw new Exception( "Destination " . $serviceDestination->DestinationId . " not found" );
        
        /*Element*/ $serviceDestinationNode = $this->findService( $serviceDestination );

        /*Element*/ $source = XmlUtil::getChild(XmlUtil::getChild($serviceDestinationNode, "properties" ), "source" );
        XmlUtil::getChild($serviceDestinationNode, "properties" )->removeChild($source);
        XmlUtil::getChild($serviceDestinationNode, "properties" )->appendChild($dom->createElement("source", $newServiceDestination->ServiceId));    
        $serviceDestinationNode->setAttribute( "id", $newServiceDestination->DestinationId );
                
        /*Element*/ $channelsElement = XmlUtil::getChild($serviceDestinationNode, "channels" );
        
        if( $channelsElement != null )
        {
        	if( $newServiceDestination->Channel == "default channel" )
        		$serviceDestinationNode->removeChild( $channelsElement );
        	else
        	{
	        	/*Element*/ $channelElement = XmlUtil::getChild(channelsElement, "channel" );
	        	
	        	if( $channelElement != null )
	        		$channelElement->setAttribute( "ref", $newServiceDestination->Channel );
	        	else
	        		
	        		$channelsElement->appendChild( $dom->createElement( "channel" )->setAttribute( "ref", $newServiceDestination->Channel ));
        	}
        }
        else if( !$newServiceDestination->Channel == "default channel" )
        {
        	$channelsElement = $dom->createElement("channels" );
        	$channelsElement->appendChild( $dom->createElement( "channel" )->setAttribute( "ref", $newServiceDestination->Channel ) );
        	$serviceDestinationNode->appendChild( $channelsElement );
        }
        
        
        /*Element*/ $securityElement = XmlUtil::getChild($serviceDestinationNode, "security" );
        /*Element*/ $rolesElement = null;
        /*Element*/ $constraintElement = null;
        
        if( count($newServiceDestination->Roles) > 0 )
        {
        	if( $securityElement != null )
            	$constraintElement = XmlUtil::getChild($securityElement, "security-constraint" ); 
        	else
        	{
        		$securityElement = $dom->createElement( "security" );
        		$constraintElement = $dom->createElement( "security-constraint" );
        		$securityElement->appendChild( $constraintElement );
        		$serviceDestinationNode->appendChild( $securityElement );        		
        	}
        	
            if( $constraintElement != null )
            	$rolesElement = XmlUtil::getChild($constraintElement, "roles" );
            else
            {
        		$constraintElement = $dom->createElement( "security-constraint" );
        		$rolesElement = $dom->createElement( "roles" );
        		$constraintElement->appendChild( $rolesElement );
        		$securityElement->appendChild( $constraintElement ); 
            }
            
            if( $rolesElement == null )
            {
            	$rolesElement = $dom->createElement( "roles" );
        		$constraintElement->appendChild( $rolesElement );
            }
            else
            {
            	/*Element*/ $role = XmlUtil::getChild($rolesElement, "role");
            	$rolesElement->removeChildren( $role );
            }
            	
            
            for( $j = 0; $j < count($newServiceDestination->Roles); $j++ )
        	{
        		/*Element*/ $role = $dom->createElement( "role" , $newServiceDestination->Roles[$j]);
        		$rolesElement->appendChild( $role );
        	}
        }  
        else
        {
        	/*Element*/ $role = XmlUtil::getChild(XmlUtil::getChild(XmlUtil::getChild($securityElement, "security-constraint" ), "roles" ), "role");
        	if(XmlUtil::getChild(XmlUtil::getChild($securityElement, "security-constraint" ), "roles" ) != null)
        		XmlUtil::getChild(XmlUtil::getChild($securityElement, "security-constraint" ), "roles" )->removeChild( $role );
        	
        }
		
        $dom->save(OrbConfig::getInstance()->getFlexConfigPath() . FlexRemotingServiceConfig::REMOTINGSERVICE_FILE);
                
        /*IDestination*/ $dest = new FlexRemotingServiceConfig();
        $dest->processDestination( ORBConfig::getInstance(), $serviceDestination->DestinationId, $serviceDestinationNode );
        ORBConfig::getInstance()->getDataServices()->getDestinationManager()->addDestination( $serviceDestination->DestinationId, $dest );    	                
    }

    private /*boolean*/function isReadOnly( ServiceDestination $serviceDestination )
    {
        return false;
    }

    public function delete( ServiceDestination $serviceDestination )
    {
    	/*Element*/ $serviceDestinationNode = $this->findService( $serviceDestination );
    	FlexRemotingServiceConfig::getConfigDoc()->documentElement->removeChild( $serviceDestinationNode );

    	FlexRemotingServiceConfig::saveConfig();
    	ORBConfig::getInstance()->getDataServices()->getDestinationManager()->removeDestination( $serviceDestination->DestinationId );   
    	ORBConfig::getInstance()->getServiceRegistry()->removeMapping( $serviceDestination->DestinationId );
    }

    private /*Element*/function &findService( ServiceDestination $serviceDestination )
    {
    	/*Element*/ $root = FlexRemotingServiceConfig::getConfigDoc()->documentElement;
        /*List*/ $children = $root->getElementsByTagName( "destination" );
    	/*Element*/ $serviceDestinationNode = null;
    	
    	for( $i = 0; $i < $children->length; $i++ )
    	{
    		$serviceDestinationNode = $children->item( $i );
    		if( $serviceDestinationNode->getAttribute( "id" ) == $serviceDestination->DestinationId )
    			return $serviceDestinationNode;
    	}
        
        if ( $serviceDestinationNode == null )
            throw new Exception( "Destination " . $serviceDestination->DestinationId . " not found" );

        return $serviceDestinationNode; 
    }

    private function checkExistance( ServiceDestination $serviceDestination )
    {
    	/*Element*/ $root = FlexRemotingServiceConfig::getConfigDoc()->documentElement;
    	/*List*/ $destinationNodes = $root->getElementsByTagName( "destination" );
    	/*Element*/ $destinationNode = null;
    	
    	for( $i = 0; $i < $destinationNodes->length; $i++ )
    	{
    		$destinationNode = $destinationNodes->item( $i );
    		
    		if( $destinationNode->getAttribute( "id" ) == $serviceDestination->DestinationId ) 
    			throw new Exception( "Destination '" . $serviceDestination->DestinationId . "' already exists" );
    	}   	
    }

    private /*void*/function validate(/*ServiceDestination*/ $serviceDestination)
    {
        if ( $serviceDestination->DestinationId == "" )
            throw new Exception( "Destination id not defined" );

        if ( $serviceDestination->ServiceId == "" )
            throw new Exception( "Destination source not defined" );
        
    } 
}
?>