<?php
/*******************************************************************
 * LoggingConfigurator.php
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
require_once(WebOrb. "Config/LoggingConfigHandler.php");
require_once("LoggingCategory.php");

class LoggingConfigurator
{
    var $loggingConfig;
    var $configNode;

    public function __construct()
    {
        $orbConfig = ThreadContext::getORBConfig();
        $this->loggingConfig = $orbConfig->getConfig("weborb/logging");
        $this->configNode = $this->loggingConfig->getConfigNode();
    }

    public function getLoggingPolicy()
    {
    	$policies = $this->loggingConfig->getLoggingPolicies();
    	$keys = array_keys($policies);
    	$resultPolicies = array();

    	foreach ($keys as $policyKey)
    	{
			$resultPolicies[$policyKey] = $policies[$policyKey]->getPolicyParameters(); 
    	}

    	$result["currentPolicy"] = $this->loggingConfig->getCurrentPolicy()->getPolicyName();
        $result["policies"] = $resultPolicies;
        
        return $result;
    }

    public function setLoggingPolicy($loggingPolicy)
    {
		$policy = LoggingPolicyFactory::getPolicyObject($loggingPolicy["Name"], $loggingPolicy["Parameters"]);
    	
        $this->loggingConfig->setCurrentPolicy($policy);
        
        $this->loggingConfig->updatePolicy($policy);
        
        return $this->loggingConfig->getCurrentPolicy()->getPolicyName();
    }

    public function getLoggingCategories()
    {
	   	$categoryNodes = $this->configNode->getElementsByTagName("log");
 
		$loggingCategoryList = array();
 
        foreach ($categoryNodes as $node)
        {
        	$loggingCategory = new LoggingCategory();
        	$loggingCategory->Name = $node->nodeValue;
        	$loggingCategory->Enable = ($node->getAttribute("enable") == "yes");
			$loggingCategoryList[] = $loggingCategory;
        }
      
        return $loggingCategoryList;
    }

    public function updateLoggingCategory(LoggingCategory $loggingCategory)
    {
        $this->loggingConfig->EnableCategory($loggingCategory->Name, $loggingCategory->Enable);
    }
}
?>