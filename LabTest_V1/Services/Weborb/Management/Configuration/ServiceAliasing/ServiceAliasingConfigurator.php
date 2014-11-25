<?php
/*******************************************************************
 * ServiceAliasingConfigurator.php
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
require_once("ServiceAlias.php");
require_once(WebOrb. "Config/ServicesConfigHandler.php");

class ServiceAliasingConfigurator
{
	var $servicesConfig;
	var $xmlNode;
	
	public function __construct()
	{
		$this->servicesConfig = new ServicesConfigHandler();
		$config = ThreadContext::getORBConfig();
		$this->servicesConfig = $config->getConfig("weborb/services");
		$this->xmlNode = $this->servicesConfig->getConfigNode();
	}
    
	public function getServiceAliases()
	{
		$serviceAliasList = array();

	    $xmlNodeList = $this->xmlNode->getElementsByTagName("service");
		
	    foreach($xmlNodeList as $node)
		{
			$serviceAlias = new ServiceAlias();
			$serviceId = $node->getElementsByTagName("serviceId")->item(0)->nodeValue;
			$name = $node->getElementsByTagName("name")->item(0)->nodeValue;
			$serviceAlias->Alias = $name; 
			$serviceAlias->Service = $serviceId;

			if (substr($serviceAlias->Service, 0, 16) == ("Weborb.Security.")
				OR substr($serviceAlias->Service, 0, 16) == ("Weborb.Dispatch.")
				OR substr($serviceAlias->Service, 0, 15) == ("Weborb.Handler."))
			{
				$serviceAlias->ReadOnly = true;				
			}
			$serviceAliasList[]=$serviceAlias;
		}
	    
		asort($serviceAliasList);

	    return $serviceAliasList;
	}

	public function create(ServiceAlias $serviceAlias)
	{
	    $this->ValidateAlias($serviceAlias);
	
	    $this->CheckExistance($serviceAlias);

		$serviceNode = $this->xmlNode->ownerDocument->createElement("service");
		$aliasNode = $this->xmlNode->ownerDocument->createElement("name");
		$serviceIdNode = $this->xmlNode->ownerDocument->createElement("serviceId");
	
	    $aliasNode->nodeValue = $serviceAlias->Alias;
	    $serviceIdNode->nodeValue = $serviceAlias->Service;
	
	    $serviceNode->appendChild($aliasNode);
	    $serviceNode->appendChild($serviceIdNode);
	
	    $this->xmlNode->appendChild($serviceNode);
	
	    $this->servicesConfig->saveConfig();    

	    return $serviceAlias;
	}	

	private function CheckExistance(ServiceAlias $serviceAlias)
	{
		$objXpath = new domxpath($this->xmlNode->ownerDocument);
		$strXpath = "//service[name = '".$serviceAlias->Alias."']";
	    $objNodeList = $objXpath->query($strXpath);
	    $alias = $objNodeList->item(0);
		if ($alias != null)
	        throw new Exception("Alias " . $serviceAlias->Alias . " already exists");
	}

	private function ValidateAlias(ServiceAlias $serviceAlias)
	{
	    if ($serviceAlias->Alias == "")
	        throw new Exception("Alias not defined");
	
	    if ($serviceAlias->Service == "")
	        throw new Exception("Service name not defined");

	    try
	    {
	        if (TypeLoader::LoadType($serviceAlias->Service) == null)
	            throw new Exception("Unable to load service " . $serviceAlias->Service);
	    }
	    catch (Exception $e)
	    {
	        throw new Exception("Service '" . $serviceAlias->Service . "' not found", $e);
	    }
	}	

	public function delete(ServiceAlias $serviceAlias)
	{
	    $serviceAliasNode = $this->FindService($serviceAlias);
	
	    $this->xmlNode->removeChild($serviceAliasNode);
	    
		$this->servicesConfig->saveConfig();
	}	

	
	private function FindService(ServiceAlias $serviceAlias)
	{
		$objXpath = new domxpath($this->xmlNode->ownerDocument);
		$strXpath = "//service[name = '".$serviceAlias->Alias."']";
		$objNodeList = $objXpath->query($strXpath);
		$serviceAliasNode = $objNodeList->item(0);
	
	    if ($serviceAliasNode == null)
	        throw new Exception("Alias " . $serviceAlias->Alias . " not found");
	
	    return $serviceAliasNode;
	}	

	public function update(ServiceAlias $serviceAlias, ServiceAlias $newServiceAlias)
	{
	    if ($this->IsReadOnly($serviceAlias))
	        throw new Exception("System alias can't be updated");
	
	    if ($serviceAlias->Alias != $newServiceAlias->Alias)
	        $this->CheckExistance($newServiceAlias);
	
	    $this->ValidateAlias($newServiceAlias);
	
	    $serviceAliasNode = $this->FindService($serviceAlias);
	
	    $serviceAliasNode->getElementsByTagName("name")->item(0)->nodeValue = $newServiceAlias->Alias;
	    $serviceAliasNode->getElementsByTagName("serviceId")->item(0)->nodeValue = $newServiceAlias->Service;
	
		$this->servicesConfig->saveConfig(); 
	}

	private function IsReadOnly(ServiceAlias $serviceAlias)
	{
		$readOnly = false;
		if (substr($serviceAlias->Service, 0, 16) == ("Weborb.Security.")
			OR substr($serviceAlias->Service, 0, 16) == ("Weborb.Dispatch.")
			OR substr($serviceAlias->Service, 0, 15) == ("Weborb.Handler."))
		{
			$readOnly = true;				
		}	
		
	    return $readOnly;
	}
	
}

?>