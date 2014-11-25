<?php
/*******************************************************************
 * SizeThresholdLogger.php
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
require_once(WebOrb . "Util/Logging/AbstractLogger.php");

class SizeThresholdLogger extends AbstractLogger
{
	private $fileName;
	private $fileNumber = 1;
	private $sizeThreshold;

	public function __construct($threashold, $fileName)
	{
		$this->sizeThreshold = $threashold * 1024;
		$this->fileNamePrefix = $fileName;
		$this->fileName = $fileName;
		
		while(true)
		{
		  if(file_exists($this->getFileName()))
		     $this->fileNumber++;
		  else
		  {
		     if($this->fileNumber > 1)
		       $this->fileNumber--;
		
		     break;
		  }
		}		
	}

	protected function getFileName()
	{
		return $this->fileNumber."_".$this->fileName;
	}

	protected function checkPolicy()
	{
		if (file_exists($this->getFilePath()) && filesize($this->getFilePath()) > $this->sizeThreshold)
		{
			$this->fileNumber++;
			if ($this->initialized)
				$this->closeFile();		
		}
	}	
}
?>