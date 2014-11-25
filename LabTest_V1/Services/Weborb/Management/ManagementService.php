<?php 
/*******************************************************************
 * ManagementService.php
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
require_once(WebOrb . "Util/ThreadContext.php");
require_once("ServiceBrowser/ServiceScanner.php");
require_once("ServiceBrowser/Service.php");
require_once("Codegen/Service/ServiceCodegenerator.php");
require_once("Codegen/Service/ServiceCodegeneratorFactory.php"); 

class ManagementService
{
	public function ping(){}
	

	public function getParent($servicePackage,$parentName)
	{
		$serviceNamespaces = explode(".",$parentName);
		if(count($serviceNamespaces) == 0)
		{
			return null;
		}
		else 
		{
				$namespaceList = array();
				for( $i = 0; $i < count($serviceNamespaces); $i++)
				{
					if( $i == 0)
					{
						$namespace = new ServiceNamespace($serviceNamespaces[$i], null);
						array_push($namespaceList, $namespace);
					}
					else
					{
						$namespace = new ServiceNamespace($serviceNamespaces[$i], $namespaceList[$i - 1] );
	    				array_push($namespace->Parent->Items,$namespace);
	    				array_push($namespaceList, $namespace);    				
					}
				}
				return $namespaceList[count($namespaceList)-1];
		}
	}
	public function getServiceChildren($servicePackage, $parentName)
	{	
		$parent = $this->getParent($servicePackage,$parentName);
		$dir = WebOrbServicesPath. str_replace(".","/",$parentName);
		$serviceScanner = new ServiceScanner();			
		return $serviceScanner->getServices($dir,$parent);
	}
	
    public function getServices()
    {
    	/*
	    if(ini_get("log_errors") == "")
		{
			ini_set("log_errors", "1");			
			ini_set("error_log", WebOrb . "orb_php_errors.txt");			
		}
		*/
        $serviceScanner = new ServiceScanner();
  
        return $serviceScanner->getServices(); 
    }
    
	public function generateCode( $className, $args, $type, $saveOnServer )
    {         
		$service = null;
		$serviceScanner = new ServiceScanner();			
		$service = $serviceScanner->findService($className);
		$codegen = ServiceCodegeneratorFactory::Create($type);
		$codegen->setService($service);
		
	    return $codegen->Generate();
    }
 }
?> 


