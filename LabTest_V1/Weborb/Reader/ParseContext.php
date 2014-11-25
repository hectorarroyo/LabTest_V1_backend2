<?php
/*******************************************************************
 * ParseContext.php
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

class ParseContext
{

    private $m_references;
    private $m_stringReferences;
    private $m_classInfos;
    private $m_version;

    public function __construct($version = null)
    {
        $this->m_references = array();
        $this->m_stringReferences = array();
        $this->m_classInfos = array();
        if (!is_null($version))
        {
            $this->m_version = $version;
        }
    }

    public function addReference(IAdaptingType $type, $index = null)
    {
        if (is_null($index))
        {
            $this->m_references[] = $type;
        }
        else
        {
            $this->m_references[$index] = $type;
        }
    }

    public function getReference($index)
    {
        return $this->m_references[$index];
    }

    public function addStringReference($refStr)
    {
        $this->m_stringReferences[] = $refStr;
    }

    public function getStringReference($index)
    {
        return $this->m_stringReferences[$index];
    }

    public function addClassInfoReference($val)
    {
        $this->m_classInfos[] = $val;
    }

    public function getClassInfoReference($index)
    {
        return $this->m_classInfos[$index];
    }

    public function getVersion()
    {
        return $this->m_version;
    }

}

?>
