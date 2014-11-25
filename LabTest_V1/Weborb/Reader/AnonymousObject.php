<?php
/*******************************************************************
 * AnonymousObject.php
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
require_once(WebOrb . "Util/Logging/Log.php");

class AnonymousObject
    implements IAdaptingType
{
    private static function canAccessFieldsDirectly()
    {
        return true;
    }

    private $m_properties;

    public function __construct(&$properties)
    {
        $this->m_properties = &$properties;
    }

    public function getProperties()
    {
        return $this->m_properties;
    }

    public function getDefaultType()
    {
        return "object";
    }

    public function defaultAdapt()
    {
        $hashtable = array();
        $keys = array_keys($this->m_properties);

        $size = count($keys);
        for ($index = 0; $index < $size; $index ++)
        {
            $obj = $this->m_properties[$keys[$index]];
            
            if ($obj instanceof IAdaptingType)
            {
                $obj = $obj->defaultAdapt();
            }

            $hashtable[$keys[$index]] = $obj;
        }

        return $hashtable;
    }

    public function adapt($type)
    {      
       if($type instanceof ReflectionClass)
            Log::log(LoggingConstants::SERIALIZATION, "Adaptation to type:" . $type->getName());

        $obj = $type->newInstance();
            
        $this->setFieldsDirect($obj, $this->m_properties);

        return $obj;
    }

    public function canAdaptTo($formalArg)
    {
        return ($formalArg instanceof ReflectionClass)
            || ("array" == $formalArg);
    }

    private function setFieldsDirect($obj, $properties)
    {    
        $type = new ReflectionClass(get_class($obj));

        do 
        {
            // Process instance fields

            $fields = $type->getProperties();
            $size = count($fields);

            for ($index = 0; $index < $size; $index ++)
            {
                if (!isset($this->m_properties[$fields[$index]->getName()]))
                    continue;
                
                $fieldValue = $this->m_properties[$fields[$index]->getName()];

                if($type->getName() == "V3Message" && $fields[$index]->getName() == "body")
                {

                    $body = null;
                  
                    if($fieldValue instanceof ArrayType)
                    {
                      $body = $fieldValue->getArray();
                    } 
                    else
                    {
                      $body = array($fieldValue);
                    }
                    
                    $bodyHolder = new BodyHolder();
                    $bodyHolder->setBody($body);
                    
                    $fieldValue = $bodyHolder;
                    
                }
                else if ($fieldValue instanceof IAdaptingType)
                {
                    if ($fieldValue instanceof NamedObject)
                        $fieldValue = $fieldValue->adapt($fieldValue->getMappedType());
                    else
                        $fieldValue = $fieldValue->defaultAdapt();
                }
                
                $fields[$index]->setValue($obj, $fieldValue);
            }

            // Process static fields
			/*
            $fields = $type->getStaticProperties();

            for ($index = 0; $index < count($fields); $index ++)
            {
                $fieldValue = $this->m_properties[$fields[$index]->getName()];

                if (is_null($fieldValue))
                    continue;
                
                if ($fieldValue instanceof IAdaptingType)
                    $fieldValue = $fieldValue->adapt($fields[$index]->getDeclaringClass());
                
                $type->setStaticPropertyValue($fields[$index]->getName(), $fieldValue);
            }
			*/

            $type = $type->getParentClass();
        }
        while (is_object($type));
    }

}

?>
