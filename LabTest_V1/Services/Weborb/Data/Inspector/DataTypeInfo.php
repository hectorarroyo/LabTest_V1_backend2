<?php
/*******************************************************************
 * DataTypeInfo.php
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
class DataTypeInfo
{
    public /*string*/ $Name;
    public /*int*/ $Length;

    public function __construct(/*string*/ $name = null, /*int*/ $length = null)
    {
    	if ($name != null && $length != null)
    	{
        	$this->Name = $name;
        	$this->Length = $length;    		
    	}
    	else if ($name != null)
    		$this->Name = $name;	
    }
}
?>