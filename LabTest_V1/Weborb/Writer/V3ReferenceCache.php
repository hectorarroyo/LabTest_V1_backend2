<?php
/*******************************************************************
 * V3ReferenceCache.php
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



require_once(WebOrb . "Writer/ReferenceCache.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Util/Logging/LoggingConstants.php");

class V3ReferenceCache
    extends ReferenceCache
{
	private $m_objectCache;
    private $m_stringCache;
	private $traitsCache;
    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->m_objectCache = array();
        $this->m_stringCache = array();  
        $this->traitsCache = array();      
    }

	public function AddToTraitsCache( /*String*/ $className )
    {
        if( $className != null && !array_key_exists($className,$this->traitsCache) )
        $this->traitsCache[ $className ] = count($this->traitsCache);
    }

    public /*bool*/function HasTraits( /*String*/ $className )
    {
        return array_key_exists($className,$this->traitsCache);
    }

    public /*int*/function GetTraitsId( /*String*/ $className )
    {
        return $this->traitsCache[ $className ];
    }
    
    public function addObject(&$obj)
    {
        if (is_string($obj))
        {
        	if(strlen($obj) == 0 )
        	 return;
        	
        	$size = count($this->m_stringCache);
        	Log::log(LoggingConstants::REF, $size . "\tADDING STRING - ".$obj);
            $this->m_stringCache[$obj] = $size;
        }
        //else if(is_array($obj))
        //{
        //	$this->m_objectCache[] = new stdClass(); 
        //}
        else
        {
            $this->m_objectCache[] = &$obj;
        }
    }

    public function getId(&$obj)
    {
        if (is_string($obj))
        {
        	$id = $this->m_stringCache[$obj];
        	
        	Log::log(LoggingConstants::REF, "GOT STRING " . $obj . "\tID - ".$id);

            return $id;
        }
        else
        {
        	$cacheSize = count($this->m_objectCache);
        	
            for ($i = 0; $i < $cacheSize; $i ++)
            {
                if ($obj === $this->m_objectCache[$i])
                {
                    return $i;
                }
            }
        }

        throw new Exception("Unknown object");
    }

    public function hasObject(&$obj, $isAmfV3Form = true)
    {
        if (is_string($obj))
        {
            return array_key_exists($obj,$this->m_stringCache);
        }
        else if(is_array($obj))
        {
        	return false;
        }
        else
        {
        	$cacheSize = count($this->m_objectCache);
        	
            for ($i = 0; $i < $cacheSize; $i ++)
            {
                if ($obj === $this->m_objectCache[$i])
                {
                    return true;
                }
            }
        }

        return false;
    }

}

?>
