<?php
/*******************************************************************
 * AbstractLogger.php
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
require_once(WebOrb . "Util/Logging/ILogger.php");
require_once(WebOrb . "Util/Logging/Log.php");

abstract class AbstractLogger implements ILogger
{
	public $mask;
	private $enabled = true;
	private $fileHandle;
	protected $initialized;
	private  $error = false;

	protected function openFile()
	{
		$logDirectoryPath = WebOrb."..".DIRECTORY_SEPARATOR."logs";

		if (!is_dir($logDirectoryPath)) 
			mkdir($logDirectoryPath);

		$this->fileHandle = @fopen($this->getFilePath(), "a+");

		if ($this->fileHandle === false)
			$this->error = true;
		else
			$this->initialized = true;
	}

	protected function closeFile()
	{
		fclose($this->fileHandle);

		$this->initialized = false;
	}

	public function log($message)
	{
		$this->checkPolicy();

		if (!$this->initialized)
			$this->openFile();

		if (!$this->error)
			fwrite($this->fileHandle, $message);
		else
			echo ($message);
	}

	protected function getFilePath()
	{
		$logDirectoryPath = WebOrb."..".DIRECTORY_SEPARATOR."logs";

        $filePath = $logDirectoryPath.DIRECTORY_SEPARATOR.$this->getFileName();

        return $filePath;
	}

	protected function checkPolicy(){}

	protected abstract function getFileName();
}
?>