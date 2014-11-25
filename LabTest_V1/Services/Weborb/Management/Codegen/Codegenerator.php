<?php
/*******************************************************************
 * Codegenerator.php
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

require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodegeneratorResult.php");
require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodeDirectory.php");
require_once(WebOrbServicesPath. "Weborb/Util/Codegen/CodeFile.php");

abstract class Codegenerator
{
	protected $file = null;
	private $folders = array();
	private $context;

	public function __construct()
	{
		$this->context = $this;
	}
	
	public function setContext(Codegenerator $codegenerator)
	{
		$this->context = $codegenerator->getContext();
	}
	
	public function getContext()
	{
		return $this->context;
	}
	
	public function Generate()
	{
		if($this->context !== $this)
			throw new Exception("Invalid call");
		
		$rootCodeItem = $this->WriteStartFolder("weborb");

		$this->doGenerate();

		$codegeneratorResult = new CodegeneratorResult();
		$codegeneratorResult->Result = $rootCodeItem;
		$codegeneratorResult->LineCount = $rootCodeItem->getLineCount();
		$codegeneratorResult->Info = $this->getInfo();

		$this->writeEndFolder();

		return $codegeneratorResult;
	}
	
	protected function generatePart(Codegenerator $childCodegenerator)
	{
		$childCodegenerator->setContext($this);
		$childCodegenerator->doGenerate();
	}

	protected abstract function doGenerate();

	protected abstract function writeHeader($name);

	protected function getInfo()
	{
		return "";
	}

	protected function writeStartFile($name)
	{
		$this->context->onWriteStartFile($name);
	}
	
	private function onWriteStartFile($name)
	{
		if($this->file != null)
			throw new Exception("End file first");

		$this->file = new CodeFile();
		$this->file->Name = $name;

		if(count($this->folders) > 0)
		{
			$this->file->Directory = $this->folders[count($this->folders)-1];
			$this->file->Directory->Items[]=$this->file;
		}

		$this->WriteCopyright($name);		
	}

	protected function writeStartFolder($name)
	{
		return $this->context->onWriteStartFolder($name);
	}
	
	private function onWriteStartFolder($name)
	{
		if($this->file != null)
			throw new Exception("End file first");

		$folder = new CodeDirectory();
		$folder->Name = $name;
		$folder->Items=array();

		if(count($this->folders) > 0)
		{
			$folder->Directory = $this->folders[count($this->folders)-1];
			$folder->Directory->Items[]=$folder;
		}

		array_push($this->folders,$folder);

		return $folder;		
	}

	protected function writeCopyright($fileName)
	{
	$text="	  /*******************************************************************
	  * $fileName
	  * Copyright (C) 2006-2008 Midnight Coders, LLC
	  *
	  * THE SOFTWARE IS PROVIDED \"AS\" IS, WITHOUT WARRANTY OF ANY KIND,
	  * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	  * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	  * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
	  * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
	  * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
	  * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	  ********************************************************************/\n";
		$this->WriteText($text);
	}

	protected function writeEndFile()
	{
		$this->context->onWriteEndFile();
	}
	
	private function onWriteEndFile()
	{
		$this->file->Content = str_replace("\r\n", "\n",$this->file->Content);
		$this->file = null;		
	}

	protected function writeEndFolder()
	{
		$this->context->onWriteEndFolder();
	}
	
	private function onWriteEndFolder()
	{
		array_pop($this->folders);
	}

	protected function writeText($text)
	{
		$this->context->onWriteText($text);
	}
	
	private function onWriteText($text)
	{
		if($this->file == null)
			throw new Exception("Start file first");

		$this->file->Content .= $text;
	}

	protected function writeLine($text)
	{
		$this->WriteText($text . "\n");
	}

	public function getCurrentURL()
	{
		$s = empty($_SERVER["HTTPS"]) ? ''
			: ($_SERVER["HTTPS"] == "on") ? "s"
			: "";
		$protocol = substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], "/")) . $s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
			: (":".$_SERVER["SERVER_PORT"]);
		return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	}


}
?>