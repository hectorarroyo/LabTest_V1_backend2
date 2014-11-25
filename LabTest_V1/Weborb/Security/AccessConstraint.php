<?php
/*******************************************************************
 * AccessConstraint.php
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
 
 require_once(WebOrb . "Exceptions/ConfigurationException.php");
 require_once(WebOrb . "Security/RoleNameRestriction.php");
 require_once(WebOrb . "Security/HostNameRestriction.php");
 require_once(WebOrb . "Security/SingleIPRestriction.php");
 require_once(WebOrb . "Security/IPRangeRestriction.php");
 
    class AccessConstraint
    {
 
        const GRANT_ACTION_NAME = "grant";
        const REJECT_ACTION_NAME = "reject";
        const GRANT_ACTION = 1;
        const REJECT_ACTION = 0;
        
        public $name;
        public $action;
        
        public $restrictions = array();
        
        //public function __construct($name,$action)
		public function AccessConstraint($name,$action)
        {
          $this->name = $name;
          
          $actionName = strtolower($action);
          
          if($actionName == AccessConstraint::GRANT_ACTION_NAME)
            $this->action = AccessConstraint::GRANT_ACTION;
          elseif($actionName == AccessConstraint::REJECT_ACTION_NAME)
            $this->action = AccessConstraint::REJECT_ACTION;
          else
            throw new ConfigurationException( "invalid access constraint action. expected 'grant' or 'reject', received " . $action );
            
        }
        
        public function getName()
        {
          return $this->name;
        }
        
        public /*string*/ function getAction()
        {
          return $this->action == 1 ? AccessConstraint::GRANT_ACTION_NAME : AccessConstraint::REJECT_ACTION_NAME;
        }
        
        public /*array*/ function getRestrictions()
        {
          return $this->restrictions;
        }
        
        public /*bool*/ function validate()
        {
          foreach($this->restrictions as $restriction)
          {
            if($restriction->pass())
            { 
              if($this->action == AccessConstraint::REJECT_ACTION)
                return false;
            }
            else
            {      
              if($this->action == AccessConstraint::GRANT_ACTION)
                return false;
            }
          }
          
          return true;
        }
        
        public /*string*/ function getReason()
        {
          foreach($this->restrictions as $restriction)
          {
            if($restriction->pass())
            {
              if($this->action == AccessConstraint::REJECT_ACTION)
                return $restriction->getDetails(false);
            }
            else
            {
              if($this->action == AccessConstraint::GRANT_ACTION)
                return $restriction->getDetails(true);
            }
          }  
          
          return "";
        }
        
        
        public /*void*/ function removeRestriction(IRestriction $restriction)
        {
            if (($i = array_search($restriction, $this->restrictions)) !== false)
                unset($this->restrictions[$i]);
        }
        
        public /*void*/ function addRestriction(IRestriction $restriction)
        {
          $this->restrictions[] = $restriction;
        }
        
        public /*void*/ function addRole($roleName)
        {
          $this->restrictions[] = new RoleNameRestriction($roleName);
        }
        
        public /*void*/ function addHostName($hostName)
        {
          $this->restrictions[] = new HostNameRestriction($hostName);
        }
        
        public /*void*/ function addIP($ipAddress)
        {
          $this->restrictions[] = new SingleIPRestriction($ipAddress);
        }
        
        public /*void*/ function addIPRange($subnetAddress, $subnetMask)
        {
          $this->restrictions[] = new IPRangeRestriction($subnetAddress, $subnetMask);
        }
   		public /*boolean*/ function isGrant()
		{
			  return $this->action == self::GRANT_ACTION;
		}
    }
 
 ?>