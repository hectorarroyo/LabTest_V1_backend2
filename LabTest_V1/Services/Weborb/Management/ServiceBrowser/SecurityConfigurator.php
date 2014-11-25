<?php 
/*******************************************************************
 * SecurityConfigurator.php
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

require_once(WebOrb. "Security/ORBSecurity.php");
require_once(WebOrb. "Util/ThreadContext.php");
require_once(WebOrb. "Security/AccessConstraint.php");
require_once(WebOrb. "Security/AclConfigHandler.php");
require_once(WebOrb. "Config/ORBConfigHandler.php");
require_once(WebOrb. "Config/ORBConfig.php");
require_once("ServiceConstraint.php"); 
require_once("ServiceRestriction.php"); 
require_once("ServiceRestrictionType.php");    


class SecurityConfigurator
{
    var $orbSecurity;
    var $securityResources;
    public $securityConfig;
    public $aclConfig;

	static private $s_instance = NULL;

    static public function getInstance()
    {
        if (is_null(self::$s_instance))
        {
            self::$s_instance = new SecurityConfigurator();
        }
        
        return self::$s_instance;
    }
    
    public function __construct()
    {
        $orbConfig = ThreadContext::getORBConfig();
        $this->orbSecurity = $orbConfig->getSecurity();
        $this->securityConfig = $orbConfig->GetConfig("weborb/security");
        $this->aclConfig = $orbConfig->GetConfig("weborb/acl");
        $this->Initialize();
    }
    
    public function Initialize()
    {
        $secureResourceNames = $this->orbSecurity->getSecureResources();
        $this->securityResources = array();
    
        foreach ($secureResourceNames as $secureResourceName)
           $this->securityResources[$secureResourceName] = $this->orbSecurity->getAccessConstraintsForResource($secureResourceName);

    }

    public function IsSecure($serviceSecurityNode)
    {
       $returnValue = false;
       
       if(array_key_exists($serviceSecurityNode->getSecureResourceName(), $this->securityResources))
             $returnValue = true;
             
       return $returnValue;  
    }

    public function LoadConstraints($serviceSecurityNode)
    {
        foreach($this->securityResources[$serviceSecurityNode->getSecureResourceName()] as $constraintName)
        {
            $serviceConstraint = new ServiceConstraint($constraintName);
            $accessConstraint=$this->orbSecurity->getAccessConstraint($constraintName);
            
            if($accessConstraint == null)
                continue;
                
            if($accessConstraint->action == 1)
                $serviceConstraint->IsGrant = true;
            else
                $serviceConstraint->IsGrant = false;
                			
    		foreach($accessConstraint->restrictions as $restriction)
            {        	
                $serviceRestriction=ServiceRestriction::CreateInstance($restriction);
                $serviceRestriction->Constraint = $serviceConstraint;
                $serviceConstraint->Restrictions[] = $serviceRestriction; 
            }
            
            $serviceSecurityNode->Constraints[] = $serviceConstraint;          
        }
    }
    
    public function applyRestriction($resource, $constraintName, $grant, $restriction)
    {
      $action = $grant ? "grant" : "reject";
      $accessConstraint = $this->orbSecurity->getAccessConstraint($constraintName);
	  $IRestriction=$this->CreateIRestriction($restriction);
      
      if ($accessConstraint != null && in_array($IRestriction, $accessConstraint->restrictions))
          throw new Exception("Restriction with the same parameters already exists");
      
      $this->orbSecurity->addRestriction($constraintName, $action, $IRestriction);	

      if (!array_key_exists($resource, $this->securityResources) || !in_array($constraintName, $this->securityResources[$resource]))
      {
          $this->securityConfig->AddResourceConstraints($resource, array( $constraintName ));
          $this->securityConfig->SaveConfig(); 
      }
      
      $this->Initialize();
    }

	public function CreateIRestriction($restriction)
	{
		$serviceRestrictionType = new ServiceRestrictionType( $restriction["Type"],"" );
		$returnRestriction=ServiceRestriction::CreateInstance2($serviceRestrictionType);
		$returnRestriction->ApplyParameters($restriction);
		
		return $returnRestriction->Convert();
	}
	
    public function removeRestriction($constraintName, $restriction)
    {
		$IRestriction=$this->CreateIRestriction($restriction);
    	$this->orbSecurity->removeRestriction($constraintName, $IRestriction);
        $this->Initialize();
    }

    public function removeConstraint( $resource, $constraintName)
    {
    	$this->orbSecurity->removeResourceConstraints($resource, array( $constraintName));
        $this->orbSecurity->UnsecureResource($resource);		
        $temp = substr($constraintName,strlen($constraintName) - strlen($resource),strlen($constraintName) - 1);
		
        if($temp == $resource)
        	$this->orbSecurity->removeAccessConstraint($constraintName);		
        
        $this->Initialize();
   	}

 	public function getRoles()
    {   
    	return ORBConfig::getInstance()->getSecurity()->getRolesProvider()->getRoles();
//    	return $this->aclConfig->getRoles();
    }
}
?> 
