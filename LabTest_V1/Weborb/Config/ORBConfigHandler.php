<?php
/*******************************************************************
 * ORBConfigHandler.php
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

 require_once(WebOrb . "Config/IConfigurationSectionHandler.php");

 abstract class ORBConfigHandler
    implements IConfigurationSectionHandler
 {
	protected $m_configNode;
    protected $m_config;
    
    
	public function create( $parent, $configContext, DOMNode $section )
	{
        $this->m_config = $configContext;
		$this->m_configNode = $section;
		
		return $this->configure( $parent, $configContext, $section );
	}

    public abstract function configure($parent, $configContext, DOMNode $section);
    
    public function setORBConfig(ORBConfig $config)
    {
      $this->m_config = $config;
    }
    
    public function getORBConfig()
    {
        return $this->m_config;  
    }

    public function getConfigNode()
    {
        return $this->m_configNode;    
    }

    public function saveConfig()
    {
      $this->m_config->Save();
    }
 }

?>