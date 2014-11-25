<?php
/*******************************************************************
 * IdentityService.php
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
class IdentityService
{
    public function HideIdentity(Identity $myIdentity)
    {
        $arrColors = array("Blue", "Brown", "Green", "Transparent", "Red");
    	
    	$myIdentity->name = str_replace("s", "sh", $myIdentity->name);
        $myIdentity->name = str_replace("o", "u", $myIdentity->name);
        $myIdentity->name = str_replace("a", "o", $myIdentity->name);
        $myIdentity->name = str_replace("j", "g", $myIdentity->name);
        $myIdentity->name = str_replace("e", "a", $myIdentity->name);
        $myIdentity->name = str_replace("y", "i", $myIdentity->name);
        $myIdentity->name = str_replace("p", "b", $myIdentity->name);
        $myIdentity->name = str_replace("r", "ch", $myIdentity->name);

        $myIdentity->eyeColor = $arrColors[rand(0, 4)];

        $myIdentity->age += rand(0, 20);

        return $myIdentity;
    }
}

class Identity
{
    var $name;
    var $age;
    var $sex;
    var $eyeColor;
}
?>