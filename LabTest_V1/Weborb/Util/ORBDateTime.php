<?php
/*******************************************************************
 * ORBDateTime.php
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

class ORBDateTime
{

    private $m_milliseconds;
    private $m_stringDateTime;
    private $m_timeZone;

    public function __construct($dateTimeMs, $timeZone)
    {
        $this->m_milliseconds = $dateTimeMs;
        $this->m_timeZone = $timeZone;
        if ($dateTimeMs == null)
        	$this->m_stringDateTime = null;
        else
        	$this->m_stringDateTime = date("n/j/Y h:i:s A", round($dateTimeMs/1000));
        
    }

    public function getTotalMs()
    {
    	return $this->m_milliseconds;
    }

    public function getDateTime()
    {
    	return $this->m_stringDateTime;
    }
    
    public function getTimeZone()
    {
        return $this->m_timeZone;
    }
}

?>
