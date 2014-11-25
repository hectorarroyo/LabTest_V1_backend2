<?php
/*******************************************************************
 * ObjectWriter.php
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



require_once(WebOrb . "Writer/ITypeWriter.php");
require_once(WebOrb . "Writer/IProtocolFormatter.php");
require_once(WebOrb . "Writer/MessageWriter.php");
require_once(WebOrb . "Util/Logging/Log.php");
require_once(WebOrb . "Writer/RemoteReferenceWriter.php");
require_once(WebOrb . "Writer/ObjectWriterCache.php");
require_once(WebOrb . "IRemote.php");

class ObjectWriter
    implements ITypeWriter
{

    private $m_remoteReferenceWriter;
    private $m_configured = false;
    private $m_serializePrivate = false;

    private $m_cache = array();

    public function __construct()
    {
      $this->m_remoteReferenceWriter = new RemoteReferenceWriter();
    }

    public function isReferenceableType()
    {
        return true;
    }

    public function write(&$obj, IProtocolFormatter $writer)
    {

      if($obj instanceof IRemote)
      {
          $this->m_remoteReferenceWriter->write($obj,$writer);

          return;
      }

      if(!$this->m_configured)
      {
        $this->m_serializePrivate = false; //ThreadContext.getORBConfig().serializePrivateFields; - skipped
        $this->m_configured = true;
      }

      $className = get_class($obj);
	  $objectFields = array();
	  $objectClass = null;
	  
	  //Log::log(LoggingConstants::INFO, "****************************************************************");
	  //Log::log(LoggingConstants::INFO, "serializingclass - ".$className);
	  
      if(!isset( $this->m_cache[$className] ))
      {
      	  $objectCache = new ObjectWriterCache();
	      $objectClass = new ReflectionClass($className);
	
	      $fullClassName = TypeLoader::getFullClassName($objectClass);
	
	      $clientSideMapping = $this->getClientClass($className);
	
	      if(!is_null($clientSideMapping))
	         $objectCache->className = $clientSideMapping;
	      else
	         $objectCache->className = $fullClassName;
	      
	     
	      $objectCache->classMeta = $objectClass;

	      $this->m_cache[$className] = $objectCache;
	      
	      $className = $objectCache->className;
      }
      else
      {
      	 $objectCache = $this->m_cache[$className];
      	 
      	 $className = $objectCache->className;
      	 $objectClass = $objectCache->classMeta;
      }

      // IAutoUpdate check - skipped

      while($objectClass)
      {
        $properties = $objectClass->getProperties();

        $propCount = count($properties);
        
         for ($i = 0; $i < $propCount; $i++)
         {
         	$prop = $properties[$i];
         	
            if( !$prop->isPublic() )
              continue;

            $propName = $prop->getName();
            
            if(!array_key_exists($propName,$objectFields))
            {
            	$val = $prop->getValue($obj);
            	//Log::log(LoggingConstants::INFO, "serializing property - ".$propName."     value ".print_r($val, true));
            	
              $objectFields[$propName] = $val;
            }
         }

       /* $properties = $objectClass->getStaticProperties();

         for ($i = 0; $i < count($properties); $i++)
         {
            if(!array_key_exists($properties[$i]->getName(),$objectFields))
            {
              $objectFields[$properties[$i]->getName()] = $properties[$i]->getValue($obj);
            }
         }                                           
	   */

         $objectClass = $objectClass->getParentClass();  
      }

      Log::log(LoggingConstants::INFO, "going to serializer" );
      $writer->getObjectSerializer()->writeObject($className, $objectFields, $writer);
      Log::log(LoggingConstants::INFO, "done with serializer" );

    }


    private function getClientClass( $className )
    {
        return Types::getClientClassForServerType($className);
    }

}

?>
