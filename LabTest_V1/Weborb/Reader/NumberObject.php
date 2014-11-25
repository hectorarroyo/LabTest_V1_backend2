<?php
/*******************************************************************
 * NumberObject.php
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

class NumberObject
    implements IAdaptingType
{

    private $m_data;

    public function __construct($data)
    {
        $this->m_data = $data;
    }

    public function getDefaultType()
    {
        return "integer";
    }

    public function defaultAdapt()
    {
        return $this->m_data;
    }

    public function adapt($type)
    {
        if ("string" == $type)
        {
            return (string) $this->m_data;
        }
        else if ("integer" == $type)
        {
            return (integer) $this->m_data;
        }
        else if ("float" == $type)
        {
            return (float) $this->m_data;
        }
        else if ("boolean" == $type)
        {
            return (boolean) $this->m_data;
        }
        else if ($type instanceof ReflectionClass)
        {
            if ($type->implementsInterface("IAdaptingType")) {
                return $this;
            }
        }
        return $this->m_data;
    }

    public function canAdaptTo($formalArg)
    {
        if (is_string($formalArg))
        {
            return ("string" == $formalArg)
                || ("float" == $formalArg)
                || ("boolean" == $formalArg)
                || ("integer" == $formalArg);
        }
        else if ($formalArg instanceof ReflectionClass)
        {
            return $formalArg->implementsInterface("IAdaptingType");
        }
        return false;
    }

}

?>
