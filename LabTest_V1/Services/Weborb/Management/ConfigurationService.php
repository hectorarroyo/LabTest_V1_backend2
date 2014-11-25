<?php
/*******************************************************************
 * ConfigurationService.php
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
require_once("Configuration/ClassMapping/ClassMappingConfigurator.php");
require_once("Configuration/ServiceAliasing/ServiceAliasingConfigurator.php");
require_once("Configuration/Logging/LoggingConfigurator.php");
require_once("Configuration/Logging/LoggingCategory.php");
require_once("Configuration/Destinations/DestinationsConfigurator.php");
require_once(WebOrb . "Config/ChannelRegistry.php");

class ConfigurationService
{
	public /*ArrayList*/function getServiceDestinations()
    {
        return DestinationsConfigurator::getInstance()->getDestinations();
    }
    
	public /*ServiceDestination*/function addServiceDestination( /*ServiceDestination*/ $serviceDestination ) 
    {
        return DestinationsConfigurator::getInstance()->create( $serviceDestination );
    }
    
    public function updateServiceDestination( ServiceDestination $serviceDestination, ServiceDestination $newServiceDestination ) 
    {
    	DestinationsConfigurator::getInstance()->update( $serviceDestination, $newServiceDestination );
    }

    public function deleteServiceDestination( ServiceDestination $serviceDestination )
    {
    	DestinationsConfigurator::getInstance()->delete( $serviceDestination );
    } 
    
	public /*ArrayList*/function getServiceRoles()
    {        
    	/*String[]*/ $roles = ORBConfig::getInstance()->getSecurity()->getRolesProvider()->getRoles();
    	/*ArrayList*/ $returnArray = array();
    	
    	for( $i = 0; $i < count($roles); $i++ )
    		$returnArray[] = $roles[ $i ];
    	
    	return $returnArray;
    }
    
    public /*ArrayList*/function getServiceChannels()
    {
    	/*ArrayList*/ $channels = ChannelRegistry::getInstance()->getChannels();
    	/*ArrayList*/ $channelNames = array();
    	$channelNames[] = "default channel";
    	
    	for( $i = 0; $i < count($channels); $i++ )
    		$channelNames[] = $channels[$i]->getId();
    	    	
        return $channelNames;
    }
    
	public function getClassMappings()
	{
		$classMappingConfigurator = new ClassMappingConfigurator();
		
		return $classMappingConfigurator->getClassMappings();
	}

	public function addClassMapping(ClassMappingItem $classMappingItem)
	{
		$classMappingConfigurator = new ClassMappingConfigurator();

		return $classMappingConfigurator->create($classMappingItem);	
	}

    public function updateClassMapping(ClassMappingItem $classMappingItem, ClassMappingItem $newClassMappingItem)
    {
    	$classMappingConfigurator = new ClassMappingConfigurator();
    	
		$classMappingConfigurator->update($classMappingItem, $newClassMappingItem);
    }

	public function deleteClassMapping(ClassMappingItem $classMappingItem)
	{
		$classMappingConfigurator = new ClassMappingConfigurator();

		$classMappingConfigurator->delete($classMappingItem);	
	}

	public function getServiceAliases()
	{
		$serviceAliasingConfigurator = new ServiceAliasingConfigurator(); 

		return $serviceAliasingConfigurator->getServiceAliases();
	}

	public function addServiceAlias(ServiceAlias $serviceAlias)
	{
		$serviceAliasingConfigurator = new ServiceAliasingConfigurator();

		return $serviceAliasingConfigurator->create($serviceAlias);
	}

	public function updateServiceAlias(ServiceAlias $serviceAlias, ServiceAlias $newServiceAlias)
	{
		$serviceAliasingConfigurator = new ServiceAliasingConfigurator();

		$serviceAliasingConfigurator->update($serviceAlias, $newServiceAlias);
	}

	public function deleteServiceAlias(ServiceAlias $serviceAlias)
	{
		$serviceAliasingConfigurator = new ServiceAliasingConfigurator();

		$serviceAliasingConfigurator->delete($serviceAlias);
	}	

	public function getLoggingPolicy()
	{
		$loggingConfigurator = new LoggingConfigurator();
		
	    return $loggingConfigurator->getLoggingPolicy();
	}
	
	public function setLoggingPolicy($loggingPolicy)
	{
		$loggingConfigurator = new LoggingConfigurator();
		
	    return $loggingConfigurator->setLoggingPolicy($loggingPolicy);
	}
	
	public function getLoggingCategories()
	{
		$loggingConfigurator = new LoggingConfigurator();
		
	    return $loggingConfigurator->getLoggingCategories();
	}
	
	public function updateLoggingCategory(LoggingCategory $loggingCategory)
	{
		$loggingConfigurator = new LoggingConfigurator();
		
	    $loggingConfigurator->updateLoggingCategory($loggingCategory);
	}

}
?>