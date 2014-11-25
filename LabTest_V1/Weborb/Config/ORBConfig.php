<?php
/*******************************************************************
 * ORBConfig.php
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
require_once(WebOrb . "Protocols/ProtocolRegistry.php");
require_once(WebOrb . "Protocols/AMFMessageFactory.php");
require_once(WebOrb . "Handlers/ObjectHandler.php");
require_once(WebOrb . "Handlers/Handlers.php");
require_once(WebOrb . "Registry/ServiceRegistry.php");
require_once(WebOrb . "Config/ServicesConfigHandler.php");
require_once(WebOrb . "Config/ClassMappingsHandler.php");
require_once(WebOrb . "Config/LoggingConfigHandler.php");
require_once(WebOrb . "Config/FlexRemotingServiceConfig.php");
require_once(WebOrb . "Config/FlexMessagingServiceConfig.php");
require_once(WebOrb . "Config/BusinessIntelligenceConfig.php");
require_once(WebOrb . "Types/Types.php");
require_once(WebOrb . "Security/ORBSecurity.php");
require_once(WebOrb . "Security/WebORBAuthorizationHandler.php");
require_once(WebOrb . "Security/SecurityConfigHandler.php");
require_once(WebOrb . "Security/AclConfigHandler.php");
require_once(WebOrb . "V3Types/core/DataServices.php");
require_once(WebOrb . "Util/Cache/Cache.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");
//require_once(realpath(WebOrb . '../Services' ) . DIRECTORY_SEPARATOR . 'Weborb' . DIRECTORY_SEPARATOR . 'Management' . DIRECTORY_SEPARATOR . 'ServiceBrowser' . DIRECTORY_SEPARATOR . 'ServiceNode.php');


class ORBConfig
{

	public $dom;

    static private /*ORBConfig*/ $s_instance = NULL;

    static public function getInstance()
    {
//        if (!is_null(self::$s_instance))
//        {
//            return self::$s_instance;
//        }
//
//    	$data = Cache::get('config');
//
//    	if ( $data instanceof ORBConfig )
//		{
//			Log::log(LoggingConstants::MYDEBUG, 'get cache');
//			self::$s_instance = $data;
//		}
//		else
//		{
//			Log::log(LoggingConstants::MYDEBUG, 'set cache');
//            self::$s_instance = new ORBConfig();
//            self::$s_instance->initialize();
//            Cache::put('config', self::$s_instance);
//		}
//		return self::$s_instance;

//    	if (is_null(self::$s_instance) && !isset($_SESSION['config']))
//        {
//            self::$s_instance = new ORBConfig();
//            self::$s_instance->initialize();
//            $_SESSION['config'] = serialize(self::$s_instance);
//        }
//        elseif(is_null(self::$s_instance))
//        {
//        	self::$s_instance = unserialize($_SESSION['config']);
//        }
//
//
//        return self::$s_instance;

        if (is_null(self::$s_instance))
        {
            self::$s_instance = new ORBConfig();
            self::$s_instance->initialize();
        }


        return self::$s_instance;
    }

    private $m_protocolRegistry;
    private $m_servicePath;
    private $m_serviceRegistry;
    private $m_logLevels;
    private $m_handlers;
    private $m_typeMapper;
    private $m_security;
    private $m_configs;
    private $businessIntelligenceConfig;
    private /*DataServices*/ $dataServices;

    private function __construct()
    {
    	$this->dataServices = new DataServices();
        $this->m_protocolRegistry = new ProtocolRegistry();
        $this->m_handlers = new Handlers();
        $this->m_typeMapper = new Types();
        $this->m_security = new ORBSecurity();

        $this->m_configs = array();
        $this->isDefConfig = true;
    }


    public /*DataServices*/function getDataServices()
    {
    	return $this->dataServices;
    }

    public function getSecurity()
    {
      return $this->m_security;
    }

    public function getConfig($configPath)
    {
      return $this->m_configs[$configPath];
    }

    public function getProtocolRegistry()
    {
        return $this->m_protocolRegistry;
    }

    public function getServicePath()
    {
        return $this->m_servicePath;
    }

    public function getLogLevels()
    {
        return $this->m_logLevels;
    }

    public function getHandlers()
    {
        return $this->m_handlers;
    }

    public function getServiceRegistry()
    {
        return $this->m_serviceRegistry;
    }

    public function getTypeMapper()
    {
      return $this->m_typeMapper;
    }

    public function getFlexConfigPath()
    {
      return WebOrb . "WEB-INF" . DIRECTORY_SEPARATOR . "flex" . DIRECTORY_SEPARATOR;
    }

    public function initialize()
    {
    	$this->dom = new DomDocument();
        $this->dom->load(WebOrb . "weborb-config.xml");
    	$this->m_servicePath = $this->dom->documentElement->getAttribute('servicePath');

    	// Initialize protocols
        $this->m_protocolRegistry->addMessageFactory(new AMFMessageFactory());


        $this->m_handlers->addInvocationHandler(new ObjectHandler());

        $this->m_serviceRegistry = new ServiceRegistry();

        $servicesConfigHandler = new ServicesConfigHandler();
        $servicesConfigHandler->configure(null, $this, $this->dom->documentElement);

        $classMappingHandler = new ClassMappingsHandler();
        $classMappingHandler->configure(null, $this, $this->dom->documentElement);

        $this->m_security->setAuthorizationHandler(new WebORBAuthorizationHandler());

        $securityConfigHandler = new SecurityConfigHandler();
        $securityConfigHandler->configure(null,$this,$this->dom->documentElement);

        $aclConfigHandler = new AclConfigHandler();
        $aclConfigHandler->configure(null,$this,$this->dom->documentElement);

        $loggingConfigHandler = new LoggingConfigHandler();
        $loggingConfigHandler->configure(null,$this,$this->dom->documentElement);

        $this->m_configs["weborb/security"] = $securityConfigHandler;
        $this->m_configs["weborb/acl"] = $aclConfigHandler;
		$this->m_configs["weborb/classMappings"] = $classMappingHandler;
        $this->m_configs["weborb/services"] = $servicesConfigHandler;
        $this->m_configs["weborb/logging"] = $loggingConfigHandler;

        $this->businessIntelligenceConfig = new BusinessIntelligenceConfig($this->dom->documentElement);
    	$this->businessIntelligenceConfig->Configure( $this->dom->documentElement, $this );

        $flexRemotingServiceConfig = new FlexRemotingServiceConfig(); //FlexRemotingServiceConfig::configure($this);
        $flexRemotingServiceConfig->Configure( $this->getFlexConfigPath(), $this );

        $flexMessagingServiceConfig = new FlexMessagingServiceConfig();
        $flexMessagingServiceConfig->Configure( $this->getFlexConfigPath(), $this );

    }

    public function Save()
    {
		 $this->dom->save(WebOrb . "weborb-config.xml");
    }

	public /*BusinessIntelligenceConfig*/function getBusinessIntelligenceConfig()
  	{
    	return self::$s_instance->businessIntelligenceConfig;
  	}
}
?>
