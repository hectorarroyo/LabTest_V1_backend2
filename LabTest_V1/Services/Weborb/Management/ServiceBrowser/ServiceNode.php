<?php
/*******************************************************************
 * ServiceNamespace.php
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

class ServiceNode
{
	const NOT_SELECTED = 0;
	const FULLY_SELECTED = 1;
	const PARTLY_SELECTED = 2;
	public $Name;
	public $Parent;
	public $Items = array();
	public $ChildrenCount;
	public $IsError = false;
	public $ErrorText;
	public /*int*/ $Selected = 0;  
    
	public function __construct()
	{
    }
    
    public function IsService()
    {
        return get_class($this) == "Service";
    }

    public function IsNamespace()
    {
        return get_class($this) == "ServiceNamespace"; 
    }
    
	public function AddChildNode($serviceNode)
	{
        $this->Items[] = $serviceNode;
        $this->ChildrenCount = count($this->Items);
    }

    public function getFullName()
    {
        $serviceNode = $this->Parent;
        $fullName = $this->Name;
        
        while($serviceNode!=null)
        {
            $fullName = $serviceNode->Name.".".$fullName;
            $serviceNode = $serviceNode->Parent;
        }
        
        return $fullName;
    }
	
    public function getPath()
    {
    	$serviceNode = $this->Parent;
        $fullName = "";
        
        while($serviceNode != null)
        {
            $fullName = $serviceNode->Name . "." . $fullName;
            $serviceNode = $serviceNode->Parent;
        }
        
        return $fullName;	
    }
    
	public /*ServiceNode*/function findItem( /*String*/ $name )
	{	
		foreach ($this->Items as $item)
		{
			if( $item->Name == $name )
				return $item;
		}
		
		return null;
	}
    
    public function deleteItem(/*String*/ $name)
    {
        for($i=0; count($this->Items)<$i; $i++)
        {
            if($this->Items[$i]->Name == $name)
            {
                unset($this->Items[$i]);
                return;
            }
        }
        
    }
	
	public /*ServiceNode*/function findItemIndex( /*String*/ $name )
	{	
		foreach ($this->Items as $key => $item)
        {
            if( $item->Name == $name )
                return $key;
        }
        
        return null;
	}
	
	public /*boolean*/ function removeItem(/*Item*/ $item)
	{
		$removeKey = '';
		foreach ($this->Items as $key => $value)
			if ($value == $item)
			{
				$removeKey = $key;
				break;
			}
		if ($removeKey !== '')
		{
			unset($this->Items[$removeKey]);
			return true;
		}
		
		return false;		
	}
}?>