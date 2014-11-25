<?php
/*******************************************************************
 * ServiceScanner.php
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

require_once("ServiceNamespace.php");
require_once("ServiceConstraint.php");
require_once("ServiceMethod.php");
require_once("Service.php");
require_once("ServiceDataType.php");
require_once("ServiceMethodArg.php");
require_once("ServiceDataTypeField.php");
require_once("SecurityConfigurator.php");
require_once( WebOrb."/Util/Logging/Log.php");

class ServiceScanner
{
    private $listNamespace;
    private $listService;
    private $list;
    private $dirname;
    private $listarray;
    private $MappingTypes;
    private $securityConfigurator;
    private $returnService;	
    
    public function __construct()
    {
        $this->MappingTypes = array();
        $this->MappingTypes["String"] = new ServiceDataType("String",null);
        $this->MappingTypes["Object"] = new ServiceDataType("Object",null);
        
        $this->securityConfigurator=SecurityConfigurator::getInstance();          	   
    }

    public function getServices($dirname = WebOrbServicesPath,$parent = null)
    {            
        $this->listService = array();
        $this->listNamespace = array();
        $this->list = array();    
        $this->getNamespaces($dirname,$parent);
        sort($this->listNamespace);
        sort($this->listService);  
        $this->list = $this->listNamespace;
        foreach ($this->listService as $item)
        {
        	$this->list[] = $item;      
        }
        return $this->list;
    }

   	public function getNamespaces($dir,$parent)
    {  
        $curdir = "";    
        $scandir = scandir($dir); 
        
        Log::log(LoggingConstants::INFO, "loading namespaces ".$dir);
              
        foreach($scandir as $entry)
        {    
        	if($entry != '.' && $entry != '..' && $entry !=".svn" && $entry !="_svn")
            {
	            if($dir == WebOrbServicesPath)
	                $curdir  = $dir.$entry;
	            else
	                $curdir  = $dir.DIRECTORY_SEPARATOR.$entry;
	                
	            if(is_dir($curdir))
	            {      
			       	$namespace = new ServiceNamespace($entry,$parent);
		                
		            if ($this->securityConfigurator->IsSecure($namespace))
		            {
		                $namespace->IsSecure = true;
		                $this->securityConfigurator->LoadConstraints($namespace);
		            }
		            $namespace->ChildrenCount = count(scandir($curdir))-2;
		            if($namespace->ChildrenCount > 0)
		               	if(is_null($parent) || $parent->Name != "Weborb" || ($parent->Name == "Weborb" && ($entry == "Management" || $entry == "Examples")))
		               	{ 
		               		$namespace->Selected = ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->isSelected($namespace->getFullName());
		                	$this->listNamespace[] = $namespace;
		               	}		                     		
			       	
	            }
	            else
	            {
	                $pieces = explode('.', $entry);
	                
	                if($pieces[count($pieces) - 1] == "php")
	                {
	                	$service = new Service($pieces[count($pieces) - 2],$parent);
	                	
	                	if ($this->securityConfigurator->IsSecure($service))
	                	{
	                    	$service->IsSecure = true;
	                    	$this->securityConfigurator->LoadConstraints($service);
	                	}	                    
	                	
//	                	$this->list[] = $service;	  

						Log::log(LoggingConstants::INFO, "doing include_once".$curdir);
	      
                    	if(!include_once($curdir))
                    	{
                    		Log::log(LoggingConstants::EXCEPTION,"Could not load file $curdir");
                    		
                    		continue;
                    	}
                    	
                    	Log::log(LoggingConstants::INFO, "loading methods");

	                    try
	                    {
	                    	if($this->loadMethods($service))
	                    	{
	                    		$service->Selected = ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->isSelected($service->getFullName());
	                    		$this->listService[] = $service;
	                    		Log::log(LoggingConstants::INFO, "service included ".$service->getFullName());
	                    	}
	                    }   
	                    catch(Exception $loadClassException)
	                    {
	                    	Log::logException(LoggingConstants::EXCEPTION,"Could not load class $curdir",$loadClassException);
	                    }                	                   
	                }
	            }
            }
        }       
    }

    public function findService($className)
    {
    	$serviceNamespaces = explode(".",$className);
    	$service = null;
    	$namespaceList = array();
   
    	if(count($serviceNamespaces) == 1)
    		$service = new Service($className,null);	
    	else
    	{
    		$current = 0;    		
    		
    		foreach($serviceNamespaces as $serviceNamespace)
    		{
    			if($current == 0){
    				$namespace = new ServiceNamespace($serviceNamespace,null);
    				$namespaceList[] = $namespace;
    			}
    			else if($current != (count($serviceNamespaces)-1))
    			{
    				$namespace = new ServiceNamespace($serviceNamespace,$namespaceList[$current-1]);
    				$namespace->Parent->Items[] = $namespace;
    				$namespaceList[] = $namespace;
    			}
    			else
    			{
    				$service = new Service($serviceNamespace,$namespaceList[$current-1]);
    				$service->Parent->Items[] = $service;
    				$namespaceList[] = $namespace;
    			}
    			$current += 1;    			
    		}    		    		
    	}
    	
    	if($this->loadServiceMethods(WebOrbServicesPath.str_replace(".",DIRECTORY_SEPARATOR,$className).".php",$service))		
    		return $service;
    }
    
    private function loadMethods($service)
    {
		$class = null;
		
    	try 
    	{
    		$class = new ReflectionClass($service->Name);
    	}
    	catch( Exception $e )
    	{
    		$service->IsError = true;
    		$service->ErrorText = substr($e->__toString(), 0, min(300,strlen($e->__toString())));
    		return true;
    	}
    	
	    $methods = $class->getMethods();
	    /*bool*/$hasPublicMethod = false;                
	    if(count($methods)>0)
	    {
	    	foreach($methods as $method)
            {
                $serviceMethod = new ServiceMethod($method->getName(),$service);
                $serviceMethod->ReturnDataType = $this->MappingTypes["Object"];
                $serviceMethod->Selected = ORBConfig::getInstance()->getBusinessIntelligenceConfig()->getMonitoredClassRegistry()->isSelected($serviceMethod->getFullName());
                $service->AddChildNode($serviceMethod);
                $params=$method->getParameters();
                
              	if ($this->securityConfigurator->IsSecure($serviceMethod))
               	{
                   	$serviceMethod->IsSecure = true;
                   	$this->securityConfigurator->LoadConstraints($serviceMethod);
               	}
                
                if(count($params) > 0)
                {
                    foreach($params as $param)
                    {
                        $serviceMethodArg = new ServiceMethodArg($param->getName(),$serviceMethod);
                        
                        if(is_null($param->getClass()))
                            $serviceMethodArg->DataType = $this->MappingTypes["String"];
                        else
                        {
                            $serviceMethodArg->DataType = $this->getType($param->getClass());                                     
                        }
                        
                        $serviceMethod->AddChildNode($serviceMethodArg);
                    }
                }
                if($method->isPublic())
                	$hasPublicMethod = true;
            } 
	    }
	    return $hasPublicMethod;
    }
    
    private function loadServiceMethods($servicePath,$service)
    {
    	include_once($servicePath);	                    
	 	return $this->loadMethods($service);
    }
    
    public function getType($reflectionClass)
    {  
        if(!array_key_exists($reflectionClass->getName(), $this->MappingTypes))
        {
       		$item = new ServiceDataType($reflectionClass->getName(),null);
        	$this->MappingTypes[$reflectionClass->getName()] = $item;
            $properties = $reflectionClass->getProperties();
            
            if(count($properties) > 0)
            {
                foreach($properties as $property)
                {   
                    $propertyMeta = new ServiceDataTypeField($property->getName(), $this->MappingTypes["String"], $item);
                    $item->AddChildNode($propertyMeta);                 
                }
            }
        }   
            
       	return $this->MappingTypes[$reflectionClass->getName()];            
    }                                                                                               
}
?>
