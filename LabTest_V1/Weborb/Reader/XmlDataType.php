<?php
/*******************************************************************
 * XmlDataType.php
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



require_once(WebOrb . "Reader/IAdaptingType.php");
require_once(WebOrb . "Exceptions/ApplicationException.php");

class XmlDataType
    implements IAdaptingType
{

    private $m_document;

    public function __construct(DomDocument $document)
    {
        $this->m_document = $document;
    }

    public function getDefaultType()
    {
        return new ReflectionClass("DomDocument");
    }

    public function defaultAdapt()
    {
        return $this->m_document;
    }

    public function adapt($type)
    {
        if ($type->isSubclassOf(new ReflectionClass("DomDocument")))
        {
            return $this->m_document;
        }
        else if ($type->isSubclassOf(new ReflectionClass("DomElement"))
                || $type->isSubclassOf(new ReflectionClass("DomNode")))
        {
            return $this->m_document->documentElement;
        }
        else
        {
            throw new ApplicationException( "unable to adapt type " . $type  . " to xml" );
        }
    }

    public function canAdaptTo($formalArg)
    {
        return FALSE;
    }

}

?>
