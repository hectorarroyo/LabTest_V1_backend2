<?php
/*******************************************************************
 * ReferenceCache.php
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


    class ReferenceCache
    {
        private $m_cache;
      

		public function __construct()
		{
			$this->reset();
		}

		public function reset()
		{
			$this->m_cache = array();
		}

		public function addObject(&$obj)
		{
			if(!is_array($obj))
           		$this->m_cache[] =& $obj;
           	else
           		$this->m_cache[] = null;
		}

		public function getId(&$obj)
		{
		  	$key = array_search($obj, $this->m_cache, true);
		  	
		    /*for($i = 0; $i < count($this->m_cache ); $i++)
		    {
              if($this->m_cache[$i] === $obj)
                return $i;
            }*/

		    if($key !== FALSE)
		    	return $key;
            
		    throw new Exception("Unknown object");
		}

		public function getObject($id)
		{
		  return $this->m_cache[$id];
		}
		
		public function hasObject( &$obj )
		{
			if(is_array($obj))
				return false;
				
			/*
		    //return in_array($obj, $this->m_cache);
		 
		    for($i = 0; $i < count($this->m_cache); $i++)
		    {
              if($this->m_cache[$i] === $obj)
                return true;
            }
            
            return false;
			*/

			return ($key = array_search($obj, $this->m_cache, true)) !== FALSE;

		}
		
    }
    
    
?>