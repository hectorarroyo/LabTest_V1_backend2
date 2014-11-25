<?php
/*******************************************************************
 * CodegeneratorResult.php
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

require_once("CodeItem.php");

class CodegeneratorResult
{
	var $Result;
    var $SavedOnServer;
    var $DownloadUri;
    var $Info;
    var $LineCount;

    private /*string*/ $m_path;
    
    public function saveToDirecorty(/*string*/ $path)
    {
    	$this->m_path = $path;
    	@mkdir($path,0777,true);
    	$this->save($this->Result->Items);
    }

    private function save($result)
    {
    	foreach ($result as $item)
    	{
    		$itemPath = str_replace("weborb".DIRECTORY_SEPARATOR."", $this->m_path, $item->getPath());
    		if ($item instanceof CodeDirectory)
    		{
    			@mkdir($itemPath,0777,true);
   		 		$this->save($item->Items);
	   		}
    		if ($item instanceof CodeFile)
    		{
				$handle = fopen($itemPath, "w");
				fwrite($handle, $item->Content);
				fclose($handle);
    		}
    	}
    }
    
}
?>