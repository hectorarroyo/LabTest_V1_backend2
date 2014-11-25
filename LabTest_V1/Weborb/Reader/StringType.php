<?php
/*******************************************************************
 * StringType.php
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



require_once(WebOrb . "Exceptions/ApplicationException.php");
require_once(WebOrb . "Reader/IAdaptingType.php");
require_once(WebOrb . "Util/Logging/Log.php");

class StringType
    implements IAdaptingType
{

    private $m_stringValue;

    public function __construct($stringValue)
    {
        $this->m_stringValue = $stringValue;
    }

    public function getDefaultType()
    {
        return "string";
    }

    public function defaultAdapt()
    {
        return $this->m_stringValue;
    }

    public function adapt($type)
    {
        if ($type instanceof ReflectionClass)
        {
            if ($type->implementsInterface("IAdaptingType")) {
                return $this;
            }
        }
        else if ("string" == $type)
        {
            return $this->m_stringValue;
        }
        else if (("boolean" == $type) && $this->isBooleanString())
        {
            return (boolean) $this->m_stringValue;
        }
        else if ($type instanceof ReflectionClass)
        {
            if ($type->implementsInterface("IAdaptingType")) {
                return $this;
            }
        }
        else if ("string" == $type)
        {
            return $this->m_stringValue;
        }
        else
        {
            throw new ApplicationException("unable to adapt string to " . $type);
        }
    }

    public function canAdaptTo($formalArg)
    {
        if (is_string($formalArg))
        {
            return ("string" == $formalArg)
                || ("boolean" == $formalArg) && $this->isBooleanString();
        }
        else if ($formalArg instanceof ReflectionClass)
        {
            return $formalArg->implementsInterface("IAdaptingType");
        }
        return FALSE;
    }

    private function isBooleanString()
    {
        $testStr = strtolower($this->m_stringValue);
        return "false" == $testStr
            || "true" == $testStr
            || "yes" == $testStr
            || "no" == $testStr
            || "0" == $testStr
            || "1" == $testStr;
    }

}

?>
