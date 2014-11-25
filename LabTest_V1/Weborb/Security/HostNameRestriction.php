<?php
/*******************************************************************
 * HostNameRestriction.php
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
require_once(WebOrb . "Security/AnyTokenComparator.php");
require_once(WebOrb . "Security/TokenComparator.php");
require_once(WebOrb . "Exceptions/ConfigurationException.php");
  
class HostNameRestriction
    implements IRestriction
{
  
    private /*string*/ $m_hostName;		
    
	private static $s_localHosts = array();
	private /*bool*/ $m_isLocalHost;
	private /*bool*/ $m_isMask;
	private $m_comparators = array();

    
    public function __construct(/*string*/ $hostName)
    {
      $this->m_hostName = $hostName;
      $this->m_isLocalHost = ($hostName == "localhost");
      $this->m_isMask = false;
      
      if($this->m_isLocalHost)
        $this->initLocalHost();
      
      $hostNameParts = split(".",$hostName);
      
      foreach($hostNameParts as $token)
      {
        $comparator = null;
        
        if($token == "*")  
        {
          $comparator = AnyTokenComparator::getInstance();
          $this->m_isMask = true;
        }
        else
          $comparator = new TokenComparator($token);
 
        $this->m_comparators[] = $comparator;
      }
    }
    
	public /*bool*/ function pass()
	{
		$remoteHost = $this->getRemoteHostName();

		if( $remoteHost == null )
		{
			Log::log( LoggingConstants::ERROR, "unable to resolve the host name for " . Network::getUserHostAddress());
			 return false;
		}

		if( $this->m_isLocalHost )
			return $this->localHostCheck( $remoteHost );

		foreach($this->m_comparators as $comparator)
		{
			$needToBreak = false;
			$index = strrpos($remoteHost,".");
			
			$token = $remoteHost;

			if( $index === false )
				$needToBreak = true;
			else
				$token = substr($remoteHost, index + 1);
				

			if( !$comparator->match( $token ) )
				return false;

			if( $needToBreak )
				break;

			$remoteHost = substr($remoteHost, 0, $index );
		}
	
        return true;				
    }
	
	public /*string*/ function getDetails(/*bool*/ $grant)
	{
      $remoteHost = Network::getUserHostName();
      
      if($remoteHost == null)
  		    return "unable to resolve the host name for " . Network::getUserHostAddress();   
  		    
      $details = "Remote host name ";
      $details .= $remoteHost;

	  if( $grant )
		$details .= " does not match ";
	  else
		$details .= " matches ";          
		
	  if($this->m_isMask)
        $details .= " the mask of a host name: ";
      else
        $details .= " the host name: ";  
         
      $details .= $this->m_hostName;
      $details .= ". access to this host has been";
       
	  if( $grant )
		$details .= " granted";
	  else
		$details .= " rejected";    
        
      return $details;           
    }
    
    private /*void*/ function initLocalHost()
    {
     self::$s_localHosts[] = Network::getServerIp();
     self::$s_localHosts[] = Network::getLocalIp();
    }
    
    public /*string*/ function getRemoteHostName()
    {
      return Network::getUserHostName();
    }
    
    private /*bool*/ function localHostCheck(/*string*/ $remoteHost)
    {
      if(strtolower($remoteHost) == "localhost")
        return true;
        
      if($remoteHost == "127.0.0.1" 
        || $remoteHost == Network::getServerIp()
        || $remoteHost == Network::getLocalIp()
        || strtolower($remoteHost) == Network::getServerName())
        
        return true;    
        
      return false;
    }
      
    public /*string*/ function getHostName()
    {
      return $this->m_hostName;
    }
    
    public /*bool*/ function isLocalHost()
    {
      return $this->m_isLocalHost;
    }   
    
    public /*bool*/ function isMask()
    {
       return $this->m_isMask;     
    }
}

?>