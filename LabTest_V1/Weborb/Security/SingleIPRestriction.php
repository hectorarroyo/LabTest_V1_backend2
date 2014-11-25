<?php
/*******************************************************************
 * SingleIPRestriction.php
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
 require_once(WebOrb . "Util/Network.php");
   
 class SingleIPRestriction
    implements IRestriction
 {
    private $m_ipAddressTokens = array();
    
	private $m_isMask;
	private $m_ipAddress;
	
	public function __construct( $ipAddress )
	{
	  $this->m_ipAddress = $ipAddress;
	  $tokens = explode(".", $ipAddress); 

	  if(count($tokens) > 4 )
		 throw new ConfigurationException( "invalid ip address - cannot parse. " + $ipAddress );

	  foreach($tokens as $token)
	  {
		$comparator = null;

		if($token == "*")
		{
			$comparator = AnyTokenComparator::getInstance();
			$this->m_isMask = true;
		}
		else
			$comparator = new TokenComparator( $token );

		$this->m_ipAddressTokens[] = $comparator;
		
      }	
	}
     
	public function pass()
	{
	  $remoteAddr = Network::getUserHostAddress();
	  $tokens = explode(".",$remoteAddr);
	  
      $i = 0;   
      
      foreach($this->m_ipAddressTokens as $tokenComparator)
      {
        if(!$tokenComparator->match($tokens[$i++]))
            return false;
      }
      
	  return true;
	}  
    
    public function getDetails($grant)
    {
      $remoteAddr = Network::getUserHostAddress();
      $details = "Remote host address ";
      $details.= $remoteAddr;
      
      if($grant)
        $details .= " does not match ";
      else
        $details .= " matches ";
      
	  if($this->m_isMask )
		$details .= " the mask of a IP address: ";
	  else
		$details .= " the IP address: ";      
	
	  $details .= $this->m_ipAddress;
	  $details .= ". access to this address has been ";	  
	  
	  if($grant)
	   $details .= " granted";
	  else
	   $details .= " rejected";
	   
	   return $details;
    }
  
    public function isMask()
    {
      return $this->m_isMask;
    }
    
    public function ipAddress()
    {
      return $this->m_ipAddress;
    }
    
 }
?>