<?php
/*******************************************************************
 * ClassMappingsHandler.php
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
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

class ClassMappingsHandler
    extends ORBConfigHandler
{
    public function configure($parent, $configContext, DOMNode $documentElement)
    {
		$this->setORBConfig($configContext);
		
	       	$section = $documentElement->getElementsByTagName("classMappings")->item(0); // check required !
	
	       	$this->m_configNode = $section;
	       
	        foreach (($section->childNodes) as $element)
	        {
	            if (!($element instanceof DOMElement))
	            {
	                continue;
	            }
	
	            $elements = $element->getElementsByTagName("clientClass");
	            $clientClass = trim($elements->item(0)->nodeValue);
	
	            $elements = $element->getElementsByTagName("serverClass");
	            $serverClass = trim($elements->item(0)->nodeValue);
	
	            $type = TypeLoader::loadType($serverClass);
	
	            Log::log(LoggingConstants::DEBUG, $clientClass . " : " . $serverClass);
	
	            ORBConfig::getInstance()->getTypeMapper()->_addClientClassMapping($clientClass, $type);
	        }
		
    }
}
?>