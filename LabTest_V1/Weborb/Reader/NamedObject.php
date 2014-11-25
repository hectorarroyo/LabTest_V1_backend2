<?php
/*******************************************************************
 * NamedObject.php
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



require_once(WebOrb . "Reader/IAdaptingType.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/TypeLoader.php");
require_once(WebOrb . "Registry/ServiceRegistry.php");
require_once(WebOrb . "Types/Types.php");

class NamedObject
    implements IAdaptingType
{

    private $m_objectName;
    private $m_typedObject;
    private $m_mappedType;

    public function __construct($objectName, IAdaptingType $typedObject)
    {
        $this->m_objectName = $objectName;
        $this->m_typedObject = $typedObject;

        try
        {
        	$this->m_mappedType = Types::getServerTypeForClientClass($objectName);
        }
        catch( Exception $ex )
        {
        }

        if($this->m_mappedType != null)
        	$serverType = $this->m_mappedType->getName();
        else
        	$serverType = "Unknown";

        Log::log(LoggingConstants::SERIALIZATION, "class: $objectName, server type: $serverType" );
    }

    public function getDefaultType()
    {
        if (!is_null($this->m_mappedType))
            return $this->m_mappedType;

        return $this->m_typedObject->getDefaultType();
    }

    public function defaultAdapt()
    {
        if (!is_null($this->m_mappedType))
            return $this->m_typedObject->adapt($this->m_mappedType);
        else
        	Log::log(LoggingConstants::SERIALIZATION, "Warning: server type for client class " . $this->m_objectName . " not found");

        return $this->m_typedObject->defaultAdapt();
    }

    public function adapt($type)
    {
        if (!is_null($this->m_mappedType) && $this->m_mappedType->isSubclassOf($type))
        {
            return $this->m_typedObject->adapt($this->m_mappedType);
        }
        return $this->m_typedObject->adapt($type);
    }

    public function canAdaptTo($formalArg)
    {
    	$formalArg = new ReflectionClass($formalArg->getName());
    	$this->m_mappedType = new ReflectionClass($this->m_mappedType->getName());
        if (!is_null($this->m_mappedType) && $this->m_mappedType instanceof ReflectionClass )
        {
            return ((($formalArg->isInterface() || $formalArg->isAbstract())
                && $this->m_mappedType->isSubclassOf($formalArg))
                || $formalArg->isSubclassOf($this->m_mappedType));
        }

        if ($formalArg->getName() == $this->m_objectName)
        {
            return true;
        }

        return false;
    }

    public function getMappedType()
    {
    	return $this->m_mappedType;
    }
}

?>
