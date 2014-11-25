<?php
/*******************************************************************
 * ORBSecurity.php
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

require_once(WebOrb . "Security/IAuthorizationHandler.php");
require_once(WebOrb . "Security/IRolesProvider.php");
require_once(WebOrb . "Exceptions/ArgumentException.php");
require_once(WebOrb . "Config/ORBConfig.php");
require_once(WebOrb . "Util/ObjectFactories.php");

class ORBSecurity
{
  
  const OPENSYSTEM_MODE = 1;
  const CLOSEDSYSTEM_MODE = 2;
  
  private $m_secureResources = array();
  private $m_userToPassword = array();
  private $m_userToRoles = array();
  private $m_accessConstraints = array();
  private $m_resourceHandlers = array();
  private $m_rolesProvider = array();
  private $m_roles = array();
  private $NO_ROLES = array();
  private /*int*/ $m_rejectedAccess;
  private /*int*/ $m_deploymentMode = 1;
  private /*IAuthorizationHandler*/ $m_authorizationHandler;


  public function __construct()
  {
	   $m_secureResources = array();
	   $m_userToPassword = array();
	   $m_userToRoles = array();
	   $m_accessConstraints = array();
	   $m_resourceHandlers = array();
	   $m_roles = array();
	   $NO_ROLES = array();
  }
  public /*void*/ function setDeploymentMode(/*string*/ $deploymentMode)
  {
    if(strtolower($deploymentMode) == "closed")
        $this->m_deploymentMode = ORBSecurity::CLOSEDSYSTEM_MODE;
  }
  
  public /*int*/ function getDeploymentMode()
  {
     return $this->m_deploymentMode;
  }
  
  public /*void*/ function setAuthorizationHandler(IAuthorizationHandler $authorizationHandler)
  {
      $this->m_authorizationHandler = $authorizationHandler;
  }
  
  public /*void*/ function setRoleHandler(IRolesProvider $roleHandler)
  {
  	$this->m_rolesProvider = $roleHandler;
  }
  
  public /*IRolesProvider*/function getRolesProvider()
  {
  	return $this->m_rolesProvider;
  }
  
  public /*IAuthenticationHandler*/ function getAuthenticationHandler( ORBConfig $config)
  {
      $securityConfigHandler = $config->getConfig( "weborb/security" );

      if( $securityConfigHandler != null )
      {
          $authenticationHandlerClassName = $securityConfigHandler->getAuthenticationHandler();

          if( $authenticationHandlerClassName != null && strlen($authenticationHandlerClassName) > 0 )
            return ObjectFactories::createServiceObject( $authenticationHandlerClassName );
      }
      
      return null;
  }
  
  public /*void*/ function secureResource(/*string*/ $resourceId, /*array*/ $constraints, /*IAuthorizationHandler*/ $authHandler)
  {
  	for($i = 0 ; $i < count($constraints); $i++)  
    {
      if(!array_key_exists($constraints[$i],$this->m_accessConstraints))
        throw new ConfigurationException( "invalid configuration - unknown constraint name in secure-resource element" );
    }
     
    $existingConstraints = null;
    
    if(array_key_exists($resourceId,$this->m_secureResources))
        $existingConstraints = $this->m_secureResources[ $resourceId ];
        
    $newConstraints = array();
        
    if($existingConstraints != null)
    {
      $existingConstraints = array_merge($existingConstraints,$constraints);
      $newConstraints = $existingConstraints;
    }
    else
      $newConstraints = $constraints;
        
      
     $this->m_secureResources[$resourceId] = $newConstraints;
     
     if($this->m_authorizationHandler != null)
        $this->m_resourceHandlers[$resourceId] = $this->m_authorizationHandler;
  }
  
  public /*void*/ function setAccessConstraints(/*array*/ $accessConstraints)
  {
    $this->m_accessConstraints = $accessConstraints;
  }
  
  public /*bool*/ function checkRole(/*string*/ $roleName)
  {
    foreach($this->m_userToRoles as $roleName)  
    {
      if(array_key_exists($roleName, $this->m_roles))
        return true;
    }
    
    return false;
  }
  
  public /*bool*/ function isInRole(/*string*/ $userName, /*string*/ $password, /*string*/ $roleName )
  {
      if($this->authenticate($userName,$password))
        return $this->hasRole($userName,$roleName);
      else
        return false;
  }

   public /*bool*/ function authenticate( /*string*/ $userName, /*string*/ $password )
   {
      if(array_key_exists($userName,$this->m_userToPassword))
	   $storedPassword = $this->m_userToPassword[ $userName ];
	  else
	   return false;
	   
	   return $password == $storedPassword;
   }
  

   public /*bool*/ function canAccessObject( $obj )
   {
       $serviceId = "";

	   if(is_string($obj))
		 $serviceId = ServiceRegistry::getMapping( $obj );
	   else
		 $serviceId = TypeLoader::getFullClassName(new ReflectionClass(get_class($obj)));

	   return $this->canAccess( $serviceId );
   } 
  
  
  //----



  // *************** ACCESS CHECK **********************************



  public /*bool*/ function canAccess( /*string*/ $resource )
  {
    Log::log(LoggingConstants::DEBUG, $resource);
    
    $resourceHandler = null;
          
    if(array_key_exists($resource,$this->m_resourceHandlers))
      $resourceHandler = $this->m_resourceHandlers[ $resource ];
    
    if($resourceHandler == null 
      && array_key_exists(ServiceRegistry::getMapping($resource),$this->m_resourceHandlers))
         $resourceHandler = $this->m_resourceHandlers[ ServiceRegistry::getMapping( $resource ) ];
    
    if($resourceHandler == null)
        $resourceHandler = $this->m_authorizationHandler;

    if($resourceHandler == null)
        return true;
    
    $accessGranted = $resourceHandler->authorizeAccess( $resource, $this );

    if(!$accessGranted )
       $this->m_rejectedAccess++;

    return $accessGranted;
   }

   public /*array*/ function getConstraints(/*string*/ $resource )
   {
          
	 if(array_key_exists(str_replace("#",".",$resource),$this->m_secureResources))
		return $this->m_secureResources[ str_replace("#",".",$resource) ];
     
//	 if(array_key_exists($resource,str_replace("#")$this->m_secureResources))
//		return $this->m_secureResources[ $resource ];	
	 
     if(strpos($resource, "*") === false && 
        array_key_exists(ServiceRegistry::getReverseMapping($resource),
                        $this->m_secureResources))
        return $this->m_secureResources[ServiceRegistry::getReverseMapping($resource)];

	 //$dotIndex = strrpos($resource,'.');
	 //if($dotIndex !== false && $resource[ $dotIndex + 1 ] == '*' )
	 //{
 	 //	$dotIndex = strpos($resource,'.',$dotIndex - 1);
     //}
	 //if($dotIndex !== false)
	 //{
	 //	$resource = substr($resource,0,$dotIndex+1 ) . "*";
	 //	
     //   return $this->getConstraints( $resource );
	 // }
	 
	 $parts = explode(".",$resource);
	 
	 if(count($parts) > 1 && $parts[count($parts)-1] == "*")
        array_pop($parts);
     
	 if(count($parts) > 1)
	 {
	    array_pop($parts);
	    
        $resource = implode(".",$parts) . ".*";
        return $this->getConstraints($resource);
     }

     if(array_key_exists("*", $this->m_secureResources))
        return $this->m_secureResources["*"];
        
	  return null;
	}

	public /*int*/ function getRejectedAccessCount()
	{
	   return $this->m_rejectedAccess;
	}

	public static /*bool*/ function IsCanAccess()
	{
	   return true;
	}
  
	private /*bool*/ function hasRole(/*string*/ $userName, /*string*/ $roleName )
	{
	  $roles = null;

	  if(array_key_exists($userName,$this->m_userToRoles))
	    $roles = $this->m_userToRoles[ $userName ];
    
	  if($roles == null )
		 return false;
      
	   return in_array($roleName,$roles);
	}
	
	// ************* SETTING UP A USER FROM ACL **********************************
	
    public /*void*/ function addUserWithRoles( /*string*/ $userName, /*string*/ $password, /*array*/ $userRoles )
    {
      $this->m_userToPassword[$userName] = $password;
      $this->m_userToRoles[$userName] = $userRoles;
      $this->m_roles = array_merge($this->m_roles,$userRoles);
    }
    
    public /*array*/ function getUsers()
    {
      return array_keys($this->m_userToPassword);
    }
    
    public /*array*/ function getRoles()
    {
      return $this->m_roles;
    }
    
    public /*array*/ function getUserRoles(/*string*/ $userName)
    {
      if(array_key_exists($userName,$this->m_userToRoles))
        return $this->m_userToRoles[$userName];
        
      return $this->NO_ROLES;
    }
    
    public /*string*/ function addUser(/*string*/ $userName, /*string*/ $password)
    {
      if(array_key_exists($userName,$this->m_userToPassword))
        throw new ArgumentException("user already exists");
        
      $this->m_userToPassword[$userName] = $password;
      
      $orbConfig = ORBConfig::getInstance();
      $aclConfig = $orbConfig->getConfig("weborb/acl");
      
      $aclConfig->addUser($userName,$password);
      $aclConfig->saveConfig();
      
      return $userName;
    }
    
    public /*string*/ function removeUser(/*string*/ $userName)
    {
      if(!array_key_exists($userName,$this->m_userToPassword))
        throw new ArgumentException( "unknown user name " . userName );
        
      unset($this->m_userToPassword[$userName]);
      unset($this->m_userToRoles[$userName]);
        
      $orbConfig = ORBConfig::getInstance();
      $aclConfig = $orbConfig->getConfig("weborb/acl");
      
      $aclConfig->removeUser($userName);
      $aclConfig->saveConfig();
      
      return $userName;
    }
    
    public /*void*/ function changePassword(/*string*/ $userName,/*string*/ $password)
    {
      if(!array_key_exists($userName,$this->m_userToPassword))
        throw new ArgumentException( "unknown user name " + userName );
        
      $this->m_userToPassword[$userName] = $password;
      
      $orbConfig = ORBConfig::getInstance();
      $aclConfig = $orbConfig->getConfig("weborb/acl");
      $aclConfig->changePassword($userName, $password);
      $aclConfig->saveConfig();
    }
    
    public /*void*/ function addRoles(/*string*/ $userName,/*array*/ $roles)
    {
      if(!array_key_exists($userName,$this->m_userToPassword))
        throw new ArgumentException( "unknown user name " . userName );
        
      $userRoles = array();
      
      if(array_key_exists($userName,$this->m_userToRoles))
        $userRoles = $this->m_userToRoles[$userName];
      
      
      for($i = 0; $i < count($roles); $i++)
      {
        if(!in_array($roles[$i],$userRoles))  
            $userRoles[] = $roles[$i];
      }
      
      $this->m_userToRoles[$userName] = $userRoles;
      
      $orbConfig = ORBConfig::getInstance();
      $aclConfig = $orbConfig->getConfig("weborb/acl");
      $aclConfig->addRoles($userName,$roles);
      $aclConfig->saveConfig();
    }
    
    public /*void*/ function removeRoles(/*array*/ $rolesToRemove)
    {
      foreach($rolesToRemove as $role)
        unset($this->m_roles[$role]);

      foreach(array_keys($this->m_userToRoles) as $userName)      
      {
        $this->removeUserRoles($userName,$rolesToRemove);
      }
      
    }
    
    public /*void*/ function removeUserRoles( /*string*/ $userName, /*array*/ $roles )
    {
      if(!array_key_exists($userName,$this->m_userToPassword))
        throw new ArgumentException( "unknown user name " . userName );
    
      $userRoles = $this->m_userRoles[$userName];
      
      foreach($roles as $role)
      {
        if(in_array($role,$userRoles))
            unset($userRoles[array_search($role,$userRoles)]);     
      }
        
      $this->m_userRoles[$userName] = $userRoles;
      
      $orbConfig = ORBConfig::getInstance();
      $aclConfig = $orbConfig->getConfig("weborb/acl");
      $aclConfig->removeRoles($userName,$roles);
    }
    
    public /*string*/ function addRole(/*string*/ $role)
    {
      $this->m_roles[] = $role;
      return $role;
    }
    
	public /*array*/ function getSecureResources()
	{
	  return array_keys($this->m_secureResources);
	}    
	
	public /*array*/ function &getConstraintsList()
	{
      return $this->m_accessConstraints;
    }
	public function setConstraintsList($key, $value)
	{
      $this->m_accessConstraints[$key] = $value;
    }
    
    public /*array*/ function getAccessConstraintsForResource(/*string*/ $resourceName)
    {
      return $this->m_secureResources[$resourceName];
    }
    
	public /*AccessConstraint*/ function getAccessConstraint(/*string*/ $constraintName )
	{
		if(array_key_exists($constraintName,$this->m_accessConstraints))
			return $this->m_accessConstraints[ $constraintName ];
				
	 	return null;
	}    
    
	public /*void*/ function addAccessConstraint( /*string*/ $constraintName, AccessConstraint $constraint )
	{
	  echo("addAccessConstraint $constraintName \n");
		
		
	  $this->m_accessConstraints[ $constraintName ] = $constraint;
	  
      $orbConfig = ORBConfig::getInstance();
      
	  $configHandler = $orbconfig->getConfig( "weborb/security" );	
      		
	  $configHandler->addAccessConstraint( $constraint );
	  $configHandler->saveConfig();
	}    
    
	public /*void*/ function removeAccessConstraint( /*string*/ $constraintName )
	{
	  unset($this->m_accessConstraints[$constraintName]);
	  
      $orbconfig = ORBConfig::getInstance();
	  $configHandler = $orbconfig->getConfig( "weborb/security" );		
	  $configHandler->removeAccessConstraint( $constraintName );
	  $configHandler->saveConfig();
	}
	
	public /*void*/ function addResourceConstraints( /*string*/ $resourceName, /*array*/ $constraintNames )
	{
		$this->secureResource( $resourceName, $constraintNames, null );
		
        $orbconfig = ORBConfig::getInstance();
		$configHandler = $orbconfig->getConfig( "weborb/security" );			
		$configHandler->addResourceConstraints( $resourceName, $constraintNames );
		$configHandler->saveConfig();
	}	
		
	public /*void*/ function removeResourceConstraints( /*string*/ $resourceName, /*array*/ $constraintNames )
	{
	   if(!array_key_exists($resourceName,$this->m_secureResources))
	       return;
	       
	   $constraints = $this->m_secureResources[ $resourceName ];
		
	   for($i = 0; $i < count($constraintNames);$i++)
	   {
	       if(in_array($constraintNames[$i],$constraints))
	           unset($constraints[array_search($constraintNames[$i],$constraints)]);
	   }
	   
        if(count($constraints) == 0)
            unset($this->secureResources[$resourceName]);
            
        $orbconfig = ORBConfig::getInstance();
		$configHandler = $orbconfig->getConfig( "weborb/security" );			
		$configHandler->removeResourceConstraints( $resourceName, $constraintNames );
		$configHandler->saveConfig();
	}
	
	public /*void*/ function addRestriction( /*string*/ $constraintName, /*string*/ $action, IRestriction $restriction )
	{
	  $accessConstraint = null;
	  
	  
	  if(array_key_exists($constraintName,$this->m_accessConstraints))  
        $accessConstraint = $this->m_accessConstraints[ $constraintName ];
      else
        $accessConstraint = new AccessConstraint($constraintName,$action);
      
      
      $accessConstraint->addRestriction($restriction);
      
      $orbconfig = ORBConfig::getInstance();
	  $configHandler = $orbconfig->getConfig( "weborb/security" );			
		
	  $configHandler->addRestriction( $constraintName, $action, $restriction );
	  //return;
	  $configHandler->saveConfig();
    }

	public /*void*/ function removeRestriction( /*string*/  $constraintName, IRestriction $restriction )
	{
		
	  if(!array_key_exists($constraintName,$this->m_accessConstraints))  
	  	throw new ArgumentException( "unknown constraint name - " . $constraintName );
	
	  $accessConstraint = $this->m_accessConstraints[ $constraintName ];

	  $accessConstraint->removeRestriction( $restriction );
	  
      $orbconfig = ORBConfig::getInstance();
	  $configHandler = $orbconfig->getConfig( "weborb/security" );			
	  $configHandler->removeRestriction( $constraintName, $restriction );
	  $configHandler->saveConfig();
	}
	
	public function UnsecureResource($resource){
		$orbconfig = ORBConfig::getInstance();
	  	$configHandler = $orbconfig->getConfig( "weborb/security" );	  	
	  	$doc = $configHandler->m_securityNode->ownerDocument;

	  	$secureResources = $doc->getElementsByTagName( "secure-resource" );

	  	$secureResourcesElement=$doc->getElementsByTagName( "secure-resources" )->item(0);
		foreach($secureResources as $secureResource){
			$resourceName=trim($secureResource->getElementsByTagName( "resource" )->item(0)->nodeValue);

			if($resourceName==$resource)
				$secureResourcesElement->removeChild($secureResource);		
		}
		
		$configHandler->saveConfig();
		
	}
  
}

?>