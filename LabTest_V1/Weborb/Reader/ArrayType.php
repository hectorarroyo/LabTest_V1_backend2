<?php
/*******************************************************************
 * ArrayType.php
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
require_once(WebOrb . "Exceptions/ApplicationException.php");

class ArrayType
    implements IAdaptingType
{

    private $m_arrayObject = array();

    public function __construct(&$arrayObject)
    {
        $this->m_arrayObject = &$arrayObject;
    }

    public function getDefaultType()
    {
        return "array";
    }

    public function defaultAdapt()
    {
        $size = sizeof($this->m_arrayObject);

        $array = array();

        for ($index = 0; $index < $size; $index ++)
        {
            $obj = $this->m_arrayObject[$index];

            if ($obj instanceof IAdaptingType)
            {
                $obj = $obj->defaultAdapt();
            }

            $array[] = $obj;
        }

        return $array;
    }

    public function adapt($type)
    {
        Log::log(LoggingConstants::DEBUG, "Adapting type: " . $type->getName());

        $size = count($this->arrayObject);

        if ("array" == $type)
        {
            $newArray = array();

            for ($index = 0; $index < $size; $index ++)
            {
                $newArray[$index] = $this->adaptArrayComponent($this->m_arrayObject[$index]);
            }

            return $newArray;
        }
        else if ("IAdaptingType" == $type->getName())
        {
            return $this;
        }
        else
        {
            return $this->defaultAdapt();
        }
    }

    public function canAdaptTo($formalArg)
    {
        return ("array" == $formalArg)
            || ($formalArg instanceof IAdaptingType);
    }

    public function getArray()
    {
        return $this->m_arrayObject;
    }

    private function adaptArrayComponent($obj)
    {
        if ($obj instanceof IAdaptingType)
        {
            return $obj->defaultAdapt();
        }
        else
        {
            throw new ApplicationException( "array element is not adapting type" );
        }
    }

}

?>
