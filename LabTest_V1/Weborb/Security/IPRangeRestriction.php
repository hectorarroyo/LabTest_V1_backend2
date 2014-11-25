<?php
/*******************************************************************
 * IPRangeRestriction.php
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
  
  class IPRangeRestriction
    implements IRestriction
  {
    private $m_subnetMask;
    private $m_subnetAddress;
    private $m_to;
    private $m_subnetAddressTokens = array(0,0,0,0);    
    private $m_endRangeTokens = array(0,0,0,0);    
    
    public function __construct($subnetAddress, $subnetMask)
    {
      $this->m_subnetAddress = $subnetAddress;
      $this->m_subnetMask = $subnetMask;
      $this->m_to = "";
      
      $tokens = explode(".",$subnetAddress);
      
	  if(count($tokens) != 4)
		throw new ConfigurationException( "unable to parse subnet address - " . $subnetAddress );
		
	  for( $i = 0; $i < 4; $i++ )
		$this->m_subnetAddressTokens[$i] = (int)$tokens[$i];
		
	  $tokens = explode(".",$subnetMask);
	  
	  if(count($tokens) != 4)
		throw new ConfigurationException( "unable to parse subnet mask - " . $subnetMask );	  
		
	  for( $i = 0; $i < 4; $i++ )
	  {
	    $subnetMaskToken = (int)$tokens[$i];
	    
	    $this->m_endRangeTokens[$i] = ~($this->m_subnetAddressTokens[$i] ^ $subnetMaskToken);
	    $this->m_to .= $this->m_endRangeTokens[$i];
		
		if($i != 3)
		  $this->m_to .= ".";
	  }	
    }
    
    public function /*bool*/ pass()
    {
      $tokens = explode(".", Network::getUserHostAddress());
      
	  $token1 = $tokens[ 0 ];
	  $token2 = $tokens[ 1 ];
	  $token3 = $tokens[ 2 ];
	  $token4 = $tokens[ 3 ];
      
      if( !($token1 >= $this->m_subnetAddressTokens[ 0 ] && $token1 <= $this->m_endRangeTokens[ 0 ]) )
		return false;

	  if( !(($token1 == $this->m_subnetAddressTokens[ 0 ] && $token2 >= $this->m_subnetAddressTokens[ 1 ]) ||
		($token1 == $this->m_endRangeTokens[ 0 ] && $token2 <= $m_endRangeTokens[ 1 ])) )
		  return false;

	  if( !(($token2 == $m_subnetAddressTokens[ 1 ] && $token3 >= $this->m_subnetAddressTokens[ 2 ]) ||
		($token2 == $this->m_endRangeTokens[ 1 ] && $token3 <= $this->m_endRangeTokens[ 2 ])) )
		   return false;

	  if( !(($token3 == $this->m_subnetAddressTokens[ 2 ] && $token4 >= $this->m_subnetAddressTokens[ 3 ]) ||
		$token3 == $this->m_endRangeTokens[ 2 ] && $token4 <= $this->m_endRangeTokens[ 3 ]))
		   return false;

		return true;
    }

    public function /*string*/ getDetails($grant)
    {
      $details = "Remote address ";
      $details .= Network::getUserHostAddress();
      
      if($grant)
        $details .= " does not belong ";   
      else
        $details .= " belongs ";   
        
        
       $details .= " to the group of computers with the following IP range:\n\tfrom: ";
       $details .= $this->m_subnetAddress;
       $details .= "\n\tto:";   
       $details .= $this->m_to;
       $details .= "\naccess to this group has been ";
       
       if($grant)
        $details .= " granted ";
       else
        $details .= " rejected ";     
        
        return $details;           
    }
    
    public function /*string*/ getSubnetAddress()
    {
      return $this->m_subnetAddress;
    }
    
    public function /*string*/ getSubnetMask()
    {
      return $this->m_subnetMask;
    }
    
    
                 
  }

 ?>