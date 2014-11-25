<?php
//require_once(WebOrbServicesPath . "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "Util". DIRECTORY_SEPARATOR . "Codegen". DIRECTORY_SEPARATOR . "CodeDirectory.php");
//require_once(WebOrbServicesPath . "Weborb" . DIRECTORY_SEPARATOR . "Management" . DIRECTORY_SEPARATOR . "Util". DIRECTORY_SEPARATOR . "Codegen". DIRECTORY_SEPARATOR . "CodeFile.php");
include_once(WebOrb . "Util" . DIRECTORY_SEPARATOR . "ZIP" . DIRECTORY_SEPARATOR . "pclzip.lib.php");

class CreateArc {

	public static $path;

	public static function createArchive($fileSystem, $name = "weborb.codegen.zip", $path = WebOrb, $libPath = "")
	{
		set_time_limit(0);
		$fileList = "";
		if($path != WebOrb)
		{
			$weborbInstallDir = substr(WebOrb,0,strlen(WebOrb)-7);
			self::createPath($weborbInstallDir, $path);
			self::$path = $weborbInstallDir . $path . DIRECTORY_SEPARATOR;
			if ($libPath != "")
			{
				$libPath = $weborbInstallDir . $libPath;
			}
		}
		else self::$path = $path;

		if(file_exists(self::$path . $name))
			unlink(self::$path . $name);

		$archive = new PclZip(self::$path . $name);

		self::createFilesAndFolders($fileSystem->Result->Items, $archive, "TempCodegen", $fileList);
		if($libPath != "")
			self::addLib($libPath, $archive);

	}

	public static function createFilesAndFolders($fileSystem, &$archive, $root, &$fileList)
	{
		mkdir(self::$path . $root, 0777, true);
		foreach($fileSystem as $item)
		{
			if($item->IsDirectory())
			{
				self::createFilesAndFolders($item->Items, $archive, $root . "/" . $item->Name, $fileList);
			}
			else
			{
				$handler = fopen(self::$path . $root . "/" . $item->Name,"w");
				fwrite($handler, $item->Content);
				fclose($handler);
				if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$archive->add(self::$path . $root . "/" . $item->Name, 
						PCLZIP_OPT_REMOVE_PATH, 
						substr(self::$path,2,strlen(self::$path)-2) . "TempCodegen");
				else
					$archive->add(self::$path . $root . "/" . $item->Name, 
						PCLZIP_OPT_REMOVE_PATH, 
						self::$path . "TempCodegen");	
				unlink(self::$path . $root . "/" . $item->Name);
			}
		}
		rmdir(self::$path . $root);
	}

	public static function createPath($weborbInstallDir, $path){
		$arrPath = explode(DIRECTORY_SEPARATOR, $path);
		$path = $weborbInstallDir;
		foreach ($arrPath as $dir)
		{
			mkdir($path . $dir . DIRECTORY_SEPARATOR);
			$path .= $dir . DIRECTORY_SEPARATOR;
		}
	}

	public static function addLib($libPath, $archive)
	{
		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$archive->add($libPath . DIRECTORY_SEPARATOR . "weborb.swc", 
				PCLZIP_OPT_REMOVE_PATH, 
				substr($libPath,2,strlen($libPath)-2), PCLZIP_OPT_ADD_PATH, "client/libs");
			$archive->add($libPath . DIRECTORY_SEPARATOR . "weborb.php", 
				PCLZIP_OPT_REMOVE_PATH, 
				substr($libPath,2,strlen($libPath)-2), 
				PCLZIP_OPT_ADD_PATH, "client/src");
		}
		else
		{
			$archive->add($libPath . DIRECTORY_SEPARATOR . "weborb.swc", 
				PCLZIP_OPT_REMOVE_PATH, 
				$libPath, PCLZIP_OPT_ADD_PATH, "client/libs");
			$archive->add($libPath . DIRECTORY_SEPARATOR . "weborb.php", 
				PCLZIP_OPT_REMOVE_PATH, 
				$libPath, 
				PCLZIP_OPT_ADD_PATH, "client/src");
		}
	}
}



?>
