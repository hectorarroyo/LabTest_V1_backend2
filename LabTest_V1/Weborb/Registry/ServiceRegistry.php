<?php
/*******************************************************************
 * ServiceRegistry.php
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



require_once(WebOrb . "Config/ORBConfig.php");

class ServiceRegistry
{

    public static function getMapping($name)
    {
        return ORBConfig::getInstance()->getServiceRegistry()->_getMapping($name);
    }

    public static function getReverseMapping($mappedName)
    {
        return ORBConfig::getInstance()->getServiceRegistry()->_getReverseMapping($mappedName);
    }

    public static function addMapping($name, $mappedName, $context = null)
    {
        ORBConfig::getInstance()->getServiceRegistry()->_addMapping(
            $name, $mappedName, (is_null($context))? array(): $context);
    }

    public static function removeMapping($name)
    {
        ORBConfig::getInstance()->getServiceRegistry()->_removeMapping($name);
    }

    public static function getContext($type)
    {
        return ORBConfig::getInstance()->getServiceRegistry()->_getContext($type);
    }

    private $m_namedServices;
    private $m_reversedMapping;
    private $m_contexts;

    public function __construct()
    {
        $this->m_namedServices = array();
        $this->m_reversedMapping = array();
        $this->m_contexts = array();
    }

    public function _getMapping($name)
    {
        if (isset($this->m_namedServices[$name]))
        {
            return $this->m_namedServices[$name];
        }
        return $name;
    }

    public function _getReverseMapping($mappedName)
    {
        if (isset($this->m_reversedMapping[$mappedName]))
        {
            return $this->m_reversedMapping[$mappedName];
        }
        return $mappedName;
    }

    public function _addMapping($name, $mappedName, $context)
    {
        $this->m_namedServices[$name] = $mappedName;
        $this->m_reversedMapping[$mappedName] = $name;
        $this->m_contexts[$mappedName] = $context;
    }

    public function _removeMapping($name)
    {
        if (isset($this->m_namedServices[$name]))
        {
            unset($this->m_namedServices[$name]);
            unset($this->m_reversedMapping[$this->m_namedServices[$name]]);
        }
    }

    public function _getContext($type)
    {
        return $this->m_contexts[$type];
    }

}

?>
