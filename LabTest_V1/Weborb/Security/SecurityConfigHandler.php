<?php
/*******************************************************************
 * SecurityConfigHandler.php
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

require_once(WebOrb . "Config/ORBConfigHandler.php");
require_once(WebOrb . "Util/Invocation.php");
require_once(WebOrb . "Util/ObjectFactories.php");
require_once(WebOrb . "Security/AccessConstraint.php");

class SecurityConfigHandler extends ORBConfigHandler
{

	public $m_securityNode;
	private $m_deploymentNode;
	private $m_authenticationHandler;
	private $m_roleHandler;
	private $m_authorizationHandler;
	private $m_accessConstraintsNode;
	private $m_secureResourcesNode;
	private $m_RESTRICTION_TYPES;

	public function __construct()
	{
		$this->m_RESTRICTION_TYPES = array();

		$this->m_RESTRICTION_TYPES["SingleIPRestriction"] = "singleIP";
		$this->m_RESTRICTION_TYPES["IPRangeRestriction"] = "ipRange";
		$this->m_RESTRICTION_TYPES["HostNameRestriction"] = "hostName";
		$this->m_RESTRICTION_TYPES["RoleNameRestriction"] = "roleName";
	}

	public function configure($parent, $configContext, DOMNode $section)
	{
		$this->setORBConfig($configContext);
		
			$section = $section->getElementsByTagName("security")->item(0);
	
			
			$this->m_securityNode = $section;
			$this->m_deploymentNode = $section->getElementsByTagName("deploymentMode")->item(0);
	
			$security = $configContext->getSecurity();
			$security->setDeploymentMode($this->m_deploymentNode->nodeValue);
	
			$this->m_authenticationHandler = $section->getElementsByTagName("authenticationHandler")->item(0);
			$this->m_authorizationHandler = $section->getElementsByTagName("authorizationHandler")->item(0);
			$this->m_roleHandler = $section->getElementsByTagName("rolesProvider")->item(0);
	
			if($this->m_authorizationHandler != null && strlen($this->m_authorizationHandler->nodeValue) != 0)
			{
				$authHandler = ObjectFactories::createServiceObject($this->m_authorizationHandler->nodeValue);
				$security->setAuthorizationHandler($authHandler);
			}
			
			if($this->m_roleHandler != null && strlen($this->m_roleHandler->nodeValue) != 0)
			{
				$roleHandler = ObjectFactories::createServiceObject($this->m_roleHandler->nodeValue);
				$security->setRoleHandler($roleHandler);
			}
	
			$this->m_accessConstraintsNode = $section->getElementsByTagName("access-constraints")->item(0);
			$this->m_secureResourcesNode = $section->getElementsByTagName("secure-resources")->item(0);
			
			$this->processConstraints($this->m_accessConstraintsNode);
			$this->processResources($this->m_secureResourcesNode);
	
		return $this;
	}

	public function getAuthenticationHandler()
	{
		if (is_string($this->m_authenticationHandler))
			return $this->m_authenticationHandler;
		return $this->m_authenticationHandler->nodeValue;
	}

	public function addAccessConstraint( AccessConstraint $constraint )
	{
		$accessConstraintElement = $this->getAccessConstraintElement( $constraint->name );
		// return;
		if($accessConstraintElement == null)
		{
			$doc = $this->m_securityNode->ownerDocument;
			$accessConstraintElement = $doc->createElement("access-constraint");
			$accessConstraintElement->setAttribute("action",$constraint->getAction());
			$nameElement = $doc->createElement("name");


			$nameElement->nodeValue = $constraint->name;

			//return;
			$accessConstraintElement->appendChild($nameElement);
			//return;
			$this->m_accessConstraintsNode->appendChild($accessConstraintElement);
		}

		//return;
		$restrictions = $constraint->getRestrictions();

		for($i = 0; $i < count($restrictions); $i++)
		{
			$restriction = $restrictions[$i];
			//////////////////////////
			$this->addRestriction2($accessConstraintElement,$restriction);

		}
	}

	public function removeAccessConstraint( $constraintName )
	{
		$accessConstraintsElement = $this->getAccessConstraintElement( $constraintName );
		if( $accessConstraintsElement != null )
			$this->m_accessConstraintsNode->removeChild( $accessConstraintsElement );
	}

	public function addResourceConstraints($resourceName, $constraintNames)
	{
		$secureResource = $this->getSecureResourceElement($resourceName);
		$doc = $this->m_securityNode->ownerDocument;

		if($secureResource == null)
		{
			$secureResource = $doc->createElement("secure-resource");
			$resource = $doc->createElement("resource");
			$resource->nodeValue = $resourceName;

			$secureResource->appendChild($resource);
			$this->m_secureResourcesNode->appendChild($secureResource);

			print("Resource constraint appended");
		}

		for($i = 0; $i < count($constraintNames); $i++)
		{
			$constraint = $doc->createElement("constraint-name");
			$constraint->nodeValue = $constraintNames[$i];
			$secureResource->appendChild($constraint);
		}
	}

	public function removeResourceConstraints($resourceName, $constraintNames)
	{
		$secureResource = $this->getSecureResourceElement( $resourceName );

		$constraints = $secureResource->getElementsByTagName( "constraint-name" );
		//$constraints = $secureResource->getElementsByTagName( "secure-resource" );
		$constraintsToRemove = $constraintNames;
		$elementsToDetach = array();

		foreach($constraints as $constraint)
		{
			$constraintName = trim($constraint->nodeValue);

			if(in_array($constraintName,$constraintsToRemove))
			$elementsToDetach[] = $constraint;
		}

		foreach($elementsToDetach as $constraint)
		$secureResource->removeChild($constraint);

		if(!$secureResource->hasChildNodes())
		$this->m_secureResourcesNode->removeChild($secureResource);

	}

	public function addRestriction($constraintName,$action, IRestriction $restriction)
	{
		$accessConstraintElement = $this->getAccessConstraintElement($constraintName);
		// return;
		if($accessConstraintElement == null)
		{
			//return;
			$accessConstraint = new AccessConstraint($constraintName,$action);
			//return;
			$accessConstraint->addRestriction($restriction);
			//return;
			$this->addAccessConstraint($accessConstraint);
		}
		else
		{
			$this->addRestriction2( $accessConstraintElement, $restriction );
		}
	}

	public function removeRestriction($constraintName, IRestriction $restriction)
	{
		$accessConstraintElement = $this->getAccessConstraintElement( $constraintName );

		if( $accessConstraintElement == null )
		throw new ArgumentException( "unknown constraint name - " + $constraintName );

		$this->removeRestriction2( $accessConstraintElement, $restriction );
			
		if(!$accessConstraintElement->hasChildNodes())
		$this->accessConstraintsNode->removeChild($accessConstraintElement);
	}

	private function addRestriction2($accessConstraintElement,IRestriction $restriction)
	{
		$element = $this->getRestrictionElement( $accessConstraintElement, $restriction );

		
		if($element != null && $element->parentNode != null)
			$accessConstraintElement->appendChild($element);
	}

	private function removeRestriction2($accessConstraintElement, IRestriction $restriction)
	{
		$element = $this->getRestrictionElement( $accessConstraintElement, $restriction );

		if( $element != null && $element->parentNode != null )
			$accessConstraintElement->removeChild( $element );
	}

	private function getRestrictionElement($accessConstraintElement,IRestriction $restriction)
	{
		/*$restrictionClassName = get_class($restriction);
		 $reflectionClass = new ReflectionClass($restrictionClassName);
		 $methodName = $this->m_RESTRICTION_TYPES[$restrictionClassName];
		 $methodInfo = $reflectionClass->getMethod($methodName);

		 return Invocation::invoke($this,$methodInfo,array($accessConstraintElement,$restriction));
		 */
		if($restriction instanceof RoleNameRestriction)
		{
			return $this->roleName($accessConstraintElement,$restriction);
		}
		if($restriction instanceof HostNameRestriction)
		{
			return $this->hostName($accessConstraintElement,$restriction);
		}
		if($restriction instanceof IPRangeRestriction)
		{
			return $this->ipRange($accessConstraintElement,$restriction);
		}
		if($restriction instanceof SingleIPRestriction)
		{
			return $this->singleIP($accessConstraintElement,$restriction);
		}
		
		throw new Exception("Unknown restriction");
	}

	private function getAccessConstraintElement($constraintName)
	{
			
		foreach($this->m_accessConstraintsNode->childNodes as $node)
		{
			if(!($node instanceof DOMElement))
			continue;

			if(strtolower($node->getElementsByTagName("name")->item(0)->nodeValue)
			== strtolower($constraintName))
			return $node;
		}

		return null;
	}

	public function getSecureResourceElement($resourceName)
	{
		foreach($this->m_secureResourcesNode->childNodes as $node)
		{
			if(!($node instanceof DOMElement))
			continue;
			if(strtolower($node->getElementsByTagName("resource")->item(0)->nodeValue)
			== strtolower($resourceName))
			return $node;
		}

		return null;
	}

	private function processResources($resources)
	{
		foreach($resources->childNodes as $node)
		{
			if(!($node instanceof DOMElement))
			continue;

			$resourceId = $this->getElementText($node,"resource");
			$constraints = $node->getElementsByTagName( "constraint-name" );
			$constraintNames = array();



			for($i = 0; $i < $constraints->length; $i++)
			$constraintNames[$i] = trim($constraints->item($i)->nodeValue);

			$resourceAuthHandlers = $node->getElementsByTagName("resourceAuthorizationHandler");
			$resourceAuthHandler = null;

			if($resourceAuthHandlers->length > 0)
			{
				$authHandlerClassName = $resourceAuthHandlers[0]->nodeValue;
				$resourceAuthHandler = ObjectFactories::createServiceObject($authHandlerClassName);
			}

			$this->getORBConfig()->getSecurity()->secureResource($resourceId,
			$constraintNames,
			$resourceAuthHandler);
		}
	}

	private function processConstraints( $constraints )
	{
		$accessConstraints = array();

		foreach($constraints->childNodes as $node)
		{
			if( !($node instanceof DOMElement) )
			continue;

			$accessConstrObj = $this->processConstraint( $node );
			$constraintName = $this->getElementText($node,"name");

			if($accessConstrObj != null)
			$accessConstraints[$constraintName] = $accessConstrObj;
		}
			
		$this->getORBConfig()->getSecurity()->setAccessConstraints($accessConstraints);
	}

	private function processConstraint($contraintElement)
	{
		Log::log(LoggingConstants::SECURITY,"Called");

		$accessConstraintName = $this->getElementText( $contraintElement, "name" );
		$action = $contraintElement->getAttribute( "action" );

		if($accessConstraintName == null)
		{
			Log::log(LoggingConstants::ERROR,"missing 'name' element in access-constraint element");
			return null;
		}

		if($action == null)
		{
			Log::log( LoggingConstants::ERROR, "missing 'action' attribute in the " . $accessConstraintName . " access-constraint element" );
			return null;
		}

		$accessConstraint = new AccessConstraint( $accessConstraintName, $action );

		$ipElements = $contraintElement->getElementsByTagName("IP");
		$this->processIPs( $ipElements, $accessConstraint );

		$ipRangeElements = $contraintElement->getElementsByTagName("IPrange");
		$this->processIPRanges( $ipRangeElements, $accessConstraint );

		$hostNameElements = $contraintElement->getElementsByTagName( "hostname" );
		$this->processHostNames( $hostNameElements, $accessConstraint );

		$roleElements = $contraintElement->getElementsByTagName( "role" );
		$this->processRoles( $roleElements, $accessConstraint );

		return $accessConstraint;
	}



	private function processIPs( $ipNodes, AccessConstraint $accessConstraint )
	{
		foreach($ipNodes as $node)
		{
			$accessConstraint->addIP( trim($node->nodeValue) );
		}
	}

	private function processIPRanges($ipRangeNodes, AccessConstraint $accessConstraint )
	{
		foreach($ipRangeNodes as $node )
		{
			if( !($node instanceof DOMElement) )
			continue;

			$subnetAddres = $this->getElementText($node, "subnet-address" );
			$subnetMask = $this->getElementText($node, "subnet-mask" );

			$accessConstraint->addIPRange( $subnetAddres, $subnetMask );
		}
	}


	private function processHostNames( $ipNodes, AccessConstraint $accessConstraint )
	{
		foreach($ipNodes as $node)
		{
		 if(!($node instanceof DOMElement) )
			continue;


			$accessConstraint->addHostName(trim( $node->nodeValue));
		}
	}

	private function processRoles( $ipNodes, AccessConstraint $accessConstraint )
	{
		foreach($ipNodes as $node)
		{
		 if(!($node instanceof DOMElement) )
			continue;

			$roleName = trim($node->nodeValue);

			if( !$this->getORBConfig()->getSecurity()->checkRole( $roleName ) )
			Log::log( LoggingConstants::INFO, "Unknown role name in access constant " . $accessConstraint->getName() . ". Role name is " .  $roleName . ". Make sure the role name is listed in acl.xml" );

			$accessConstraint->addRole( $roleName );
		}
	}

	private function getElementText( $element, $elementName )
	{
		return trim($element->getElementsByTagName( $elementName )->item(0)->nodeValue);
	}


	private function singleIP( $accessConstraintElement, $restriction )
	{
		//$ipAddress = $restriction->getIPAddress();
		$ipAddress = $restriction->ipAddress();
			
		$ipAddresses = $accessConstraintElement->getElementsByTagName( "IP" );

		foreach($ipAddresses as $ipAddressElement)
		{
		 if(!($ipAddressElement instanceof DOMElement) )
			continue;

			if( trim($ipAddressElement->nodeValue) == $ipAddress)
			return $ipAddressElement;
		}
			
		$addressElement = $accessConstraintElement->ownerDocument->createElement( "IP" );
		$addressElement->nodeValue = $ipAddress;
		$accessConstraintElement->appendChild($addressElement);
			
		return $addressElement;
	}

	private function ipRange( $accessConstraintElement, $restriction )
	{
		$subnetAddress = $restriction->getSubnetAddress();
		$subnetMask = $restriction->getSubnetMask();
		$ipRanges = $accessConstraintElement->getElementsByTagName( "IPrange" );
			
		foreach($ipRanges as $ipRangeElement)
		{
		 if(!($ipRangeElement instanceof DOMElement) )
			continue;

		 if($this->getElementText($ipRangeElement,"subnet-address") == $subnetAddress &&
		 $this->getElementText($ipRangeElement,"subnet-mask") == $subnetMask)
		 {
		 	return $ipRangeElement;
		 }
		}
			
		$rangeElement = $accessConstraintElement->ownerDocument->createElement( "IPrange" );
		$subnetAddressElement = $accessConstraintElement->ownerDocument->createElement( "subnet-address" );
		$subnetMaskElement = $accessConstraintElement->ownerDocument->createElement( "subnet-mask" );
		$subnetAddressElement->nodeValue = $subnetAddress;			
		$subnetMaskElement->nodeValue = $subnetMask;
		
		$rangeElement->appendChild( $subnetAddressElement );
		$rangeElement->appendChild( $subnetMaskElement );
		
		$accessConstraintElement->appendChild($rangeElement);	
		
		return $rangeElement;
			
	}

	private function hostName( $accessConstraintElement, $restriction )
	{
		$hostName = $restriction->getHostName();
		$hostNames = $accessConstraintElement->getElementsByTagName( "hostname" );

		foreach($hostNames as $hostNameElement)
		{
		 if(!($hostNameElement instanceof DOMElement) )
			continue;

		 if(trim($hostNameElement->nodeValue) == $hostName)
		 return $hostNameElement;
		}
		$hNameElement = $accessConstraintElement->ownerDocument->createElement( "hostname" );
		
		$hNameElement->nodeValue = $hostName;
		$accessConstraintElement->appendChild($hNameElement);

		return $hNameElement;
	}

	public function roleName( $accessConstraintElement, $restriction )
	{
		$roleName = $restriction->getRoleName();
		$roleNames = $accessConstraintElement->getElementsByTagName( "role" );

		foreach($roleNames as $roleNameElement)
		{
			if( !($roleNameElement instanceof DOMElement) )
			continue;

			if(trim($roleNameElement->nodeValue) == $roleName)
			return $roleNameElement;
		}

		$rNameElement = $accessConstraintElement->ownerDocument->createElement( "role" );
		$rNameElement->nodeValue = $roleName;
		
		$accessConstraintElement->appendChild($rNameElement);
		//$rNameElement->parentNode = ;
		
		return $rNameElement;
	}

}

?>