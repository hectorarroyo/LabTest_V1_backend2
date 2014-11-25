<?php
/*******************************************************************
 * DateType.php
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



require_once(WebOrb . "Util/ORBDateTime.php");
require_once(WebOrb . "Reader/IAdaptingType.php");

class DateType
    implements IAdaptingType
{

    private $m_date;

    public function __construct(ORBDateTime $dateTime)
    {
        $this->m_date = $dateTime;
    }

    public function getDefaultType()
    {
        return new ReflectionClass("ORBDateTime");
    }

    public function defaultAdapt()
    {
        return $this->m_date;
    }

    public function adapt($type)
    {
    	if($type instanceof ReflectionClass)
    	{
    		if($type->getName() == "DateTime")
    			return $this->adaptToDateTime();     

    		return $this->defaultAdapt();	
    	}
    	else if($type == "DateTime")
			return $this->adaptToDateTime(); 
    		
        return $this->defaultAdapt();
    }

    private function adaptToDateTime()
    {
    	return new DateTime(date("Y-m-d\TH:i:s\Z", $this->m_date->getTotalMs() / 1000));	
    }
    
    public function canAdaptTo($formalArg)
    {
    	if(is_string($formalArg))
    	{
    		return $formalArg == "DateTime" || $formalArg == "ORBDateTime";
    	}
        else if ($formalArg instanceof ReflectionClass)
        {
            return ("ORBDateTime" == $formalArg->getName() || "DateTime" == $formalArg->getName());
        }

        return false;
    }

}

?>