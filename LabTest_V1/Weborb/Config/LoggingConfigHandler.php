<?php
/*******************************************************************
 * LoggingConfigHandler.php
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
require_once(WebOrb . "Config/ORBConfigHandler.php");
require_once(WebOrb . "ORBConstants.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");
require_once(WebOrb . "Util/Logging/Policies/LoggingPolicyFactory.php");

class LoggingConfigHandler extends ORBConfigHandler
{
	private $currentPolicy;
	private $loggingPolices = array();
	private $channels = array();
	
	public function configure($parent, $configContext, DOMNode $documentElement)
	{
		$section = $documentElement->getElementsByTagName("logging")->item(0);

		$this->m_configNode = $section;
		$this->setORBConfig($configContext);
		
		$currentPolicyName = null;
		$categories = array();

		foreach($section->childNodes as $node)
		{
		    if (!($node instanceof DOMElement))
            {
                continue;
            }	
			
			switch($node->nodeName)
			{
				case ORBConstants::CURRENT_POLICY:
					$currentPolicyName = $node->nodeValue;
					break;
				case ORBConstants::LOGGING_POLICY:
					
					$this->processPolicy($node);
					break;
				case ORBConstants::LOG:
					if($node->getAttribute(ORBConstants::ENABLE) == ORBConstants::YES)
						$categories[] = trim($node->nodeValue);
						
					break;
			}
		}

		$this->currentPolicy = $this->loggingPolices[$currentPolicyName];

		if($this->currentPolicy != null)
		{
			Log::log(LoggingConstants::DEBUG, "adding logger with current policy: " +  $currentPolicyName );

			Log::setLogger($this->currentPolicy->getLogger());
		}
		else
			Log::log(LoggingConstants::ERROR, "the current policy value of " + $this->currentPolicy + " does not match any policyName elements" );


		foreach($categories as $category)
			Log::startLogging(LoggingConstants::getChannelByName($category));

		return $this;
	}

	private function processPolicy($policyNode)
	{
		$policyName = null;
		$initParams = array();

		foreach($policyNode->childNodes as $node)
		{
			switch($node->nodeName)
			{
				case ORBConstants::POLICY_NAME:
					$policyName = $node->nodeValue;
					break;

				case ORBConstants::PARAMETER:
					$name = $node->getElementsByTagName("name")->item(0)->nodeValue;
					$initParams[$name] = $node->getElementsByTagName("value")->item(0)->nodeValue;
					break;
			}
		}

		$this->loggingPolices[$policyName] = LoggingPolicyFactory::getPolicyObject($policyName, $initParams);
	}

	public function getCurrentPolicy()
	{
		return $this->currentPolicy;
	}

    public function EnableCategory($category, $enabled)
    {
		$configNode = $this->m_configNode->getElementsByTagName(ORBConstants::LOG);

		foreach ($configNode as $node)
		{
			if ($node->nodeValue == $category)
			{
				$node->setAttribute(ORBConstants::ENABLE, $enabled ? ORBConstants::YES : ORBConstants::NO);
				$this->saveConfig();

	        if ($enabled)
			    Log::startLogging(LoggingConstants::getChannelByName($category));
			else
			    Log::stopLogging(LoggingConstants::getChannelByName($category));
				
				return;
			}
		}
    }

	public function setCurrentPolicy(ILoggingPolicy $policy)
	{
		$this->currentPolicy = $policy;
		
		$this->m_configNode->getElementsByTagName("currentPolicy")->item(0)->nodeValue = $policy->getPolicyName();

		$this->saveConfig();
		
		Log::setLogger($policy->getLogger());
	}
	
	public function updatePolicy(ILoggingPolicy $policy)
	{
		$loggingPolicyNodes = $this->m_configNode->getElementsByTagName("loggingPolicy");
		foreach ($loggingPolicyNodes as $node)
		{
			if ($node->getElementsByTagName("policyName")->item(0)->nodeValue == $policy->getPolicyName())
			{
				if ($policy instanceof SizeThresholdPolicy)
				{
					$parameter = $policy->getPolicyParameters();
					
					$parameterNode = $node->getElementsByTagName("parameter");
					foreach ($parameterNode as $nodeParam)
					{
						if ($nodeParam->getElementsByTagName("name")->item(0)->nodeValue == "fileSize")
						{
							$nodeParam->getElementsByTagName("value")->item(0)->nodeValue = $parameter["fileSize"];
						}
						else if ($nodeParam->getElementsByTagName("name")->item(0)->nodeValue == "fileName")
						{
							$nodeParam->getElementsByTagName("value")->item(0)->nodeValue = $parameter["fileName"];
						}
					}

					$this->saveConfig();
				}
				else if ($policy instanceof SpecificFilePolicy)
				{
					$parameter = $policy->getPolicyParameters();

					$parameterNode = $node->getElementsByTagName("parameter")->item(0); 
					$parameterNode->getElementsByTagName("value")->item(0)->nodeValue = $parameter["fileName"];

					$this->saveConfig();
				}
				break;
			}
		}
	}

	public function getLoggingPolicies()
	{
		return $this->loggingPolices;
	}
}
?>