<?php
/*******************************************************************
 * Types.php
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

	class Types
	{
		private $m_abstractMappings;
        private $m_clientMappings;
        private $m_serverMappings;
        
        public function __construct()
        {
          $this->m_abstractMappings = array();
          $this->m_clientMappings = array();
          $this->m_serverMappings = array();
        }
        
        
        public static function getAbstractClassMapping( $type )
        {
            return ORBConfig::getInstance()->getTypeMapper()->_getAbstractClassMapping( $type );
        }

		public function _getAbstractClassMapping( $type )
		{
			return $this->m_abstractMappings[ $type ];
		}

        public static function addAbstractTypeMapping($abstractType,ReflectionClass $mappedType )
        {
            ORBConfig::getInstance()->getTypeMapper()->_addAbstractTypeMapping( $abstractType, $mappedType );
        }

		public function _addAbstractTypeMapping($abstractType, $mappedType )
		{
			$this->m_abstractMappings[ $abstractType ] = $mappedType;
		}

        public static function addClientClassMapping($clientClass, ReflectionClass $mappedServerType )
        {
            ORBConfig::getInstance()->getTypeMapper()->_addClientClassMapping( $clientClass,  $mappedServerType );
        }

        public function _AddClientClassMapping( $clientClass, ReflectionClass $mappedServerType )
        {
            $this->m_clientMappings[ $clientClass ] = $mappedServerType;
            $this->m_serverMappings[ $mappedServerType->getName() ] = $clientClass;
        }

        public static function getServerTypeForClientClass( $clientClass )
        {
            return ORBConfig::getInstance()->getTypeMapper()->_getServerTypeForClientClass( $clientClass );
        }

        public function _getServerTypeForClientClass( $clientClass )
        {
          if(array_key_exists($clientClass,$this->m_clientMappings))
            return $this->m_clientMappings[ $clientClass ];

          return TypeLoader::loadType($clientClass);
        }

        public static function getClientClassForServerType( $serverClassName )
        {
            return ORBConfig::getInstance()->getTypeMapper()->_getClientClassForServerType( $serverClassName );
        }

        public function _getClientClassForServerType( $serverClassName )
        {
            if(array_key_exists($serverClassName,$this->m_serverMappings))
                return $this->m_serverMappings[ $serverClassName ];
                
            return null;
        }
	}


?>