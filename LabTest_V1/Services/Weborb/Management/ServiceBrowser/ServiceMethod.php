<?php
/*******************************************************************
 * ServiceMethod.php
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

require_once("ServiceSecurityNode.php");

class ServiceMethod extends ServiceSecurityNode
{
	public $ReturnDataType; 
		
	public function __construct()
    {
        $args = func_get_args();
        if(func_num_args() == 2)
        	$this->initParam($args[0], $args[1]);
        else 
        	$this->init();
    } 
    
    public function init()
    {
    	
    }
    
    public function initParam($name, $parent)
    {
        $this->Name = $name;
        $this->Parent = $parent;
        $this->IsSecure = $parent->IsSecure;  
    }   
}
?>