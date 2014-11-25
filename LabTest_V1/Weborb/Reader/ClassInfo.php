<?php
/*******************************************************************
 * ClassInfo.php
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



class ClassInfo
{

    private $m_looseProps;
    private $m_className;
    private $m_props = array();

    public function __construct($looseProps, $className)
    {
        $this->m_looseProps = $looseProps;
        $this->m_className = $className;
    }

    public function addProperty($propName)
    {
        $this->m_props[] = $propName;
    }

    public function getPropertyCount()
    {
        return count($this->m_props);
    }

    public function getProperty($index)
    {
        return $this->m_props[$index];
    }

    public function getClassName()
    {
        return $this->m_className;
    }

    public function getLooseProps()
    {
        return $this->m_looseProps;
    }

}

?>
