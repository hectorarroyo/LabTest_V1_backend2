<?php
/*******************************************************************
 * FileSystemItem.php
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
class FileSystemItem
{
	var $Name;
    var $fullName;
    var $createdOn;
    var $lastAccessedOn;
    var $lastWrittenOn;

	public function getAtribute($path, $name)
	{
		$info = stat($path.$name);
		$this->Name = $name;
		$this->fullName = $path.$name."/";
		$this->createdOn = new ORBDateTime($info[10]*1000, 0);
		$this->lastAccessedOn =  new ORBDateTime($info[8]*1000, 0);
		$this->lastWrittenOn = new ORBDateTime($info[9]*1000, 0);
	}
}
?>