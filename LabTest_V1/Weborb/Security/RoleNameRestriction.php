<?php
/*******************************************************************
 * RoleNameRestriction.php
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

  require_once(WebOrb . "Security/IRestriction.php");
  require_once(WebOrb . "Exceptions/ConfigurationException.php");

  class RoleNameRestriction
      implements IRestriction
  {
    private $m_roleName;

    public function __construct($roleName)
    {
      $this->m_roleName = $roleName;
    }

    public function pass()
    {
    	$credentials = ThreadContext::currentCallerCredentials();
    	
    	if( $credentials != null )
        {
	        /*String[]*/ $userRoles = ORBConfig::getInstance()->getSecurity()->getRolesProvider()->getUserRoles( $credentials->getUserId() );
			print_r($userRoles);
			Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
        	for($i = 0; $i < count($userRoles); $i++ )
	          if( $userRoles[ $i ] == $this->m_roleName )
	            return true;
		
        }
		Log::log(LoggingConstants::MYDEBUG, "No roles");
      return false;
//      $credentials = ThreadContext::currentCallerCredentials();
//
//      Log::log( LoggingConstants::DEBUG, "got credentials - " . ($credentials == null));
//
//      if($credentials == null)
//      {
//      Log::log( LoggingConstants::DEBUG, "credentials are null ");
//		// return false, authorization is required
//		return false;
//      }
//
//      $userName = $credentials->getUserId();
//      $password = $credentials->getPassword();
//
//      $orbConfig = ORBConfig::getInstance();
//      $security = $orbConfig->getSecurity();
//
//      Log::log( LoggingConstants::DEBUG, "username " . $userName . "     password " . $password ."    role " . $this->m_roleName);
//
//
//      return  $security->isInRole($userName, $password, $this->m_roleName);

    }

	public function getDetails($grant)
	{
	  $reason = "Access to the resource has been rejected because the user ";

	  if( $grant )
		$reason .= "does not have";
	  else
		$reason .= "has" ;

	  $reason .= " credentials for the required role name";

	   return $reason;
	}

	public function getRoleName()
	{
      return $this->m_roleName;
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
     }

  }
?>