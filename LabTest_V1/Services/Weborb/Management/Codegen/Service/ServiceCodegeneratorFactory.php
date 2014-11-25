<?php
/*******************************************************************
 * CodegeneratorFactory.php
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

require_once("AS2Codegenerator.php");
require_once("AS3Codegenerator.php");
require_once("AS2InlineCodegenerator.php");
require_once("AS2ARPFrameworkCodegenerator.php");
require_once("AS3ARPFrameworkCodegenerator.php");
require_once("CairngormFrameworkCodegenerator.php");
require_once("FlashCommCodegenerator.php");
require_once("AJAXCodegenerator.php");
require_once("pureMVC.php");

class ServiceCodegeneratorFactory
{
	public static function Create($type)
	{
		switch($type)
		{
			case 0:
				return new AS3Codegenerator();
			case 1:
				return new AS2Codegenerator();
			case 2:
				return new AS2InlineCodegenerator();
			case 3:
				return new AS2ARPFrameworkCodegenerator();
			case 4:
				return new AS3ARPFrameworkCodegenerator();
			case 5:
				return new CairngormFrameworkCodegenerator();
			case 6:
				return new FlashCommCodegenerator();
			case 7:
				return new AJAXCodegenerator();	
			case 8:
				return new pureMVC();		
			default:
				throw new Exception("Unknown generator type");  
		}
	}
}
?>
