<?php
/*******************************************************************
 * CodeItem.php
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

require_once("CodeFile.php");
require_once("CodeDirectory.php");

class CodeItem
{
	var $Name;
	var $Directory;
        
    public function IsDirectory()
    {
    	if(get_class($this) == "CodeDirectory")
    	
        	return true;
        else
        
            return false;
    }

    public function IsFile()
    {
        if(get_class($this) == "CodeFile")
        
	       	return true;
	    else
	    
	       	return false;
    }
	
    public function getPath()
    {
        $path = $this->Name;
        $codeDirectory = $this->Directory;
        while ($codeDirectory != null)
        {
        	$path = $codeDirectory->Name . DIRECTORY_SEPARATOR . $path;
            $codeDirectory = $codeDirectory->Directory;
        }
        if ($this->IsDirectory())
            $path .= DIRECTORY_SEPARATOR;
            
        return $path;
    }

    public function find($fullname)
    {
    	if ($this->Name == $fullname)
    	
        	return $this;
        	
        $result = null;
        
        if ($this->IsDirectory())
        {
        	foreach ($this->Items as $item)
            {
            	$result = $item->Find($fullname);
                if ($result != null)
                
               		break;
            }
        }
        
       	return $result;
    }
}
?>