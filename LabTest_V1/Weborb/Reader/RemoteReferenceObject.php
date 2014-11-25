<?php
/*******************************************************************
 * RemoteReferenceObject.php
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

class RemoteReferenceObject
    implements IAdaptingType
{

    private $m_reference;

    public function __construct($reference)
    {
        $this->m_reference = $reference;
    }

    public static function createReference($referencedObject)
    {
        $referenceId = get_class($referencedObject) . "_proxy_" . uniqid();
        $proxy = new RemoteReferenceObject($referenceId);
        $_SESSION[$referenceId] = $referencedObject;
        return $proxy;
    }

    public function getDefaultType()
    {
        throw new ApplicationException("TBD");
    }

    public function defaultAdapt()
    {
        return $this->m_reference;
    }

    public function adapt($type)
    {
        return ObjectFactories::createServiceObject($type);
    }

    public function canAdaptTo($formalArg)
    {
        return ($formalArg instanceof ReflectionClass);
    }

    public function getServiceID()
    {
        return $this->m_reference;
    }

}

?>
