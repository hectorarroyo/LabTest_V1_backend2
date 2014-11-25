<?php
/*******************************************************************
 * ClassMappingConfigurator.php
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
require_once("ClassMappingItem.php");
require_once(WebOrb. "Config/ClassMappingsHandler.php");

class ClassMappingConfigurator
{
	var	$classMappingsConfig;
	var $xmlNode;

	public function __construct()
	{
		$this->classMappingsConfig = new ClassMappingsHandler();
		$config = ThreadContext::getORBConfig();
		$this->classMappingsConfig = $config->getConfig("weborb/classMappings");
		$this->xmlNode = $this->classMappingsConfig->getConfigNode();
	}

	public function getClassMappings()
	{
		$classMappingItemList = array();

		$titles = $this->xmlNode->getElementsByTagName("classMapping");
		foreach($titles as $node)
		{
			$client = $node->getElementsByTagName("clientClass")->item(0)->nodeValue;
			$server = $node->getElementsByTagName("serverClass")->item(0)->nodeValue;
			$classMappingItem = new ClassMappingItem();
			$classMappingItem->ClientClass = $client;
			$classMappingItem->ServerClass = $server;

			if (substr($client, 0, 5) == ("flex."))
			{
				$classMappingItem->ReadOnly = true;				
			}
			$classMappingItemList[] = $classMappingItem;
		}
		
		return $classMappingItemList;
	}

	public function create(ClassMappingItem $classMappingItem)
	{
		$this->Validate($classMappingItem);

		$xmlClassMappingNode = $this->xmlNode->ownerDocument->createElement("classMapping");
		$xmlClientClass = $this->xmlNode->ownerDocument->createElement("clientClass");
		$xmlServerClass = $this->xmlNode->ownerDocument->createElement("serverClass");

		$xmlClientClass->nodeValue = $classMappingItem->ClientClass;
		$xmlServerClass->nodeValue = $classMappingItem->ServerClass;

		$xmlClassMappingNode->appendChild($xmlClientClass);
		$xmlClassMappingNode->appendChild($xmlServerClass);

		$this->xmlNode->appendChild($xmlClassMappingNode);

		$this->classMappingsConfig->saveConfig();

		return $classMappingItem;
	}

	private function Validate(ClassMappingItem $newClassMappingItem)
	{
		if ($newClassMappingItem->ServerClass == "")
			throw new Exception("Server class not defined");

		if ($newClassMappingItem->ClientClass == "")
			throw new Exception("Client class not defined");

		try
		{
			if (TypeLoader::loadType($newClassMappingItem->ServerClass) == null)
				throw new Exception("Unable to load server type " . $newClassMappingItem->ServerClass);
		}
		catch (Exception $e)
		{
			throw new Exception("Server type " . $newClassMappingItem->ServerClass . " not found");
		}
	}	

	public function delete(ClassMappingItem $classMappingItem)
	{
		$classMappingNode = $this->FindNode($classMappingItem);

		$this->xmlNode->removeChild($classMappingNode);

		$this->classMappingsConfig->saveConfig();
	}

	private function FindNode(ClassMappingItem $classMappingItem)
	{
		$objXpath = new domxpath($this->xmlNode->ownerDocument);
		$strXpath="//classMapping[clientClass = '".$classMappingItem->ClientClass."' and serverClass = '".$classMappingItem->ServerClass."']";
		$objNodeList = $objXpath->query($strXpath);
		$classMappingNode = $objNodeList->item(0);

		if ($classMappingNode == null)
			throw new Exception("Class mapping between '" . $classMappingItem->ClientClass . "' and '" . $classMappingItem->ServerClass . "' not found");
			
		return $classMappingNode;
	}

	public function update(ClassMappingItem $classMappingItem, ClassMappingItem $newClassMappingItem)
	{
		$classMappingNode = $this->FindNode($classMappingItem);

		$this->Validate($newClassMappingItem);

		$classMappingNode->getElementsByTagName("clientClass")->item(0)->nodeValue = $newClassMappingItem->ClientClass;

		$classMappingNode->getElementsByTagName("serverClass")->item(0)->nodeValue = $newClassMappingItem->ServerClass;

		$this->classMappingsConfig->saveConfig();
	}
}
?>