<?php
/*******************************************************************
 * FlexRemotingServiceConfig.php
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


 require_once(WebOrb . "Util/Invocation.php");
 require_once(WebOrb . "Util/ObjectFactories.php");
 require_once(WebOrb . "Util/XmlUtil.php");
 require_once(WebOrb . "Security/AccessConstraint.php");
 require_once(WebOrb . "Config/BaseFlexConfig.php");
 require_once(WebOrb . "V3Types/core/RemotingDestination.php");

class FlexRemotingServiceConfig extends BaseFlexConfig
{
	const REMOTINGSERVICE_FILE = "remoting-config.xml";
	private static /*ArrayList*/ $services = array();
	private static /*Document*/ $configDoc;
	public function __construct()
	{
		self::$services = array();
	}
    public function preConfig()
    {
    	try
        {
            /*DestinationManager*/ $destinationManager = $this->orbConfig->getDataServices()->getDestinationManager();

            if( count(self::$services) != 0 )                        	            
                for( $i = 0; $i < count(self::$services); $i++ )
            	{
            		/*String*/ $serviceId = self::$services[$i];
                    /*RemotingDestination*/ $remotingDestination = $destinationManager->getDestination( $serviceId );

                    if( $remotingDestination == null )
                        continue;

                    ORBSecurity::unsecureResource( $remotingDestination->serviceId );
                    $destinationManager->removeDestination( $serviceId );
                    $this->orbConfig->getServiceRegistry()->_removeMapping( $serviceId );                
            	}
        }
        catch( Exception $exception )
        {
            Log::log( LoggingConstants::EXCEPTION, $exception->getMessage());
        }
    }

    public /*String*/function getConfigFileName()
    {
        return "remoting-config.xml";
    }

    public /*String*/function getDefaultServiceHandlerName()
    {
        return null;//"Weborb.V3Types.core.RemotingHandler";
    }

    public /*IDestination*/function processDestination( ORBConfig $orbConfig, /*String*/ $destinationId, /*Element*/ $xmlElement )
    {
    	/*Element*/ $props = $xmlElement->getElementsByTagName( "properties" )->item( 0 );
        /*String*/ $source = $props->getElementsByTagName( "source" )->item( 0 )->textContent;
        /*String*/ $scope = null;

        if( $props->getElementsByTagName( "scope" )->length > 0 )
        	$scope = $props->getElementsByTagName( "scope" )->item( 0 )->textContent;//props.getAttributeValue( "scope" );

        /*Hashtable*/ $context = null;

        if( $scope != null && strlen(trim($scope)) > 0 )
        {
            $context = array();
            $context[ORBConstants::ACTIVATION] = $scope;
        }

        Log::log( LoggingConstants::INFO, "Registered Flex Remoting destination - " . $destinationId );
        $orbConfig->getServiceRegistry()->_addMapping( $destinationId, $source, $context );

        /*List*/ $securityNodes = $xmlElement->getElementsByTagName( "security" );

    	if( $securityNodes != null && $securityNodes->length > 0 )
        {
            /*Element*/ $securityElement = $securityNodes->item( 0 );
            /*Element*/ $securityConstraintNode = $securityElement->getElementsByTagName( "security-constraint" )->item( 0 );
            /*Element*/ $rolesNode = $securityConstraintNode->getElementsByTagName( "roles" )->item( 0 );
            /*List*/ $rolesNodeList = $rolesNode->getElementsByTagName( "role" );
            /*AccessConstraint*/ $constraint = new AccessConstraint( $source . "_constraint", "grant" );

            for( $i = 0; $i < $rolesNodeList->length; $i++ )
                $constraint->addRole( $rolesNodeList->item(0)->textContent );

			   	 $security = $orbConfig->getSecurity();
	             $constraintsList = &$security->getConstraintsList();
	             $constraintsList[$constraint->getName()] = $constraint;

	             $constraints = array($constraint->getName());


	             $security->secureResource($source,$constraints,null);
        }

        array_push(self::$services, $destinationId);
        $remotingDestination = new RemotingDestination( $destinationId, $source );
        return new RemotingDestination( $destinationId, $source );
    }

    public function postConfig()
    {
    }
	
    public static /*Document*/function getConfigDoc()
    {
    	if( self::$configDoc != null )
   	 		return self::$configDoc;
        
		self::$configDoc = new DomDocument();
        self::$configDoc->load(OrbConfig::getInstance()->getFlexConfigPath() . self::REMOTINGSERVICE_FILE );  
        return self::$configDoc;
    }
    
    public static function saveConfig()
    {
    	self::$configDoc->save(OrbConfig::getInstance()->getFlexConfigPath() . self::REMOTINGSERVICE_FILE );
//    	echo self::$configDoc->saveXml();exit;
    }
//    public static function configure($orbConfig)
//    {
//        $dom = new DomDocument();
//        $dom->load($orbConfig->getFlexConfigPath() . FlexRemotingServiceConfig::REMOTINGSERVICE_FILE);
//
//		$root = $dom->documentElement;
//		$destinationNodes = $root->getElementsByTagName( "destination" );
//
//        foreach($destinationNodes as $destElement)
//        {
//
//          $destinationId = $destElement->getAttribute( "id" );
//          $props = $destElement->getElementsByTagName( "properties" )->item(0);
//          $source = XmlUtil::getElementText($props,"source");
//          $scope = XmlUtil::getElementText( $props, "scope" );
//
//          $context = array();
//
//          if($scope != null && strlen(trim($scope)) > 0)
//          {
//              $context[ORBConstants::ACTIVATION] = $scope;
//          }
//
//          $orbConfig->getServiceRegistry()->_addMapping($destinationId, $source, $context );
//
//
//          $securityNodes = $destElement->getElementsByTagName( "security" );
//
//          if($securityNodes != null && $securityNodes->length > 0 )
//          {
//
//             $securityElement = $securityNodes->item(0);
//             $securityConstraintNode = $securityElement->getElementsByTagName( "security-constraint" )->item(0);
//             $rolesNode = $securityConstraintNode->getElementsByTagName( "roles" )->item(0);
//             $rolesNodeList = $rolesNode->getElementsByTagName( "role" );
//             $constraint = new AccessConstraint($source . "_constraint", "grant" );
//
//             for($i = 0; $i < $rolesNodeList->length; $i++)
//                $constraint->addRole($rolesNodeList->item($i)->nodeValue);
//
//             $security = $orbConfig->getSecurity();
//             $constraintsList = &$security->getConstraintsList();
//             $constraintsList[$constraint->getName()] = $constraint;
//
//             $constraints = array($constraint->getName());
//
//
//             $security->secureResource($source,$constraints,null);
//
//          }
//        }
//    }
}
?>