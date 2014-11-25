<?php
/*******************************************************************
 * MessageWriter.php
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
require_once(WebOrb . "Writer/NullWriter.php");
require_once(WebOrb . "Writer/NumberWriter.php");
require_once(WebOrb . "Writer/BooleanWriter.php");
require_once(WebOrb . "Writer/StringWriter.php");
require_once(WebOrb . "Writer/ArrayWriter.php");
require_once(WebOrb . "Writer/ObjectWriter.php");
require_once(WebOrb . "Writer/DateWriter.php");
require_once(WebOrb . "Writer/AMFMessageWriter.php");
require_once(WebOrb . "Writer/AMFHeaderWriter.php");
require_once(WebOrb . "Writer/AMFBodyWriter.php");
require_once(WebOrb . "Writer/ReferenceWriter.php");
require_once(WebOrb . "Writer/BoundPropertyBagWriter.php");
require_once(WebOrb . "Writer/BodyHolderWriter.php");
require_once(WebOrb . "Writer/StdClassWriter.php");
require_once(WebOrb . "Writer/ResourceWriter.php");
require_once(WebOrb . "Writer/DataTableAsListWriter.php");
require_once(WebOrb . "Writer/ORBDateTimeWriter.php");
require_once(WebOrb . "Writer/ArrayObjectWriter.php");
require_once(WebOrb . "Writer/ORBXMLWriter.php");
require_once(WebOrb . "Util/IDatabaseReader.php");

class MessageWriter
{
	private static $initialized = false;
    private static $s_referenceWriter;
	private static $s_array = array();
	
	private static $m_defaultWriter;
    private static $m_nullWriter;
    private static $m_numberWriter;
    private static $m_booleanWriter;
    private static $m_stringWriter;
    private static $m_messageWriter;
    private static $m_headerWriter;
    private static $m_bodyWriter;
    private static $m_arrayWriter;
    private static $m_dateWriter;
    private static $m_orbdateWriter;
    private static $m_writers = array();
    private static $m_mapWriter;
    private static $m_bodyHolderWriter;
    private static $m_stdClassWriter;
    private static $m_resourceWriter;
    private static $m_dataTableAsListWriter;
    private static $m_arrayObjectWriter;
    private static $m_xmlWriter;

	
    public static function init()
    {
    	if( self::$initialized )
    		return;
    		
            self::$s_referenceWriter = new ReferenceWriter();
            
	       self::$m_defaultWriter = new ObjectWriter();
	        self::$m_nullWriter = new NullWriter();
	        self::$m_numberWriter = new NumberWriter();
	        self::$m_stringWriter = new StringWriter(false);
	        self::$m_arrayWriter = new ArrayWriter();
	        self::$m_dateWriter = new DateWriter( FALSE );
	        self::$m_booleanWriter = new BooleanWriter();
	        self::$m_messageWriter = new AMFMessageWriter();
	        self::$m_headerWriter = new AMFHeaderWriter();
	        self::$m_bodyWriter = new AMFBodyWriter();
	        self::$m_mapWriter = new BoundPropertyBagWriter();
	        self::$m_bodyHolderWriter = new BodyHolderWriter();
	        self::$m_stdClassWriter = new StdClassWriter();
	        self::$m_resourceWriter = new ResourceWriter();
	        self::$m_dataTableAsListWriter = new DataTableAsListWriter();
			self::$m_orbdateWriter = new ORBDateTimeWriter( FALSE );
			self::$m_arrayObjectWriter = new ArrayObjectWriter();
			self::$m_xmlWriter = new ORBXMLWriter();
	
	        self::$m_writers['NULL'] = self::$m_nullWriter;
	        self::$m_writers['integer'] = self::$m_numberWriter;
	        self::$m_writers['double'] = self::$m_numberWriter;
	        self::$m_writers['boolean'] = self::$m_booleanWriter;
	        self::$m_writers['string'] = self::$m_stringWriter;
	        self::$m_writers['Request'] = self::$m_messageWriter;
	        self::$m_writers['Header'] = self::$m_headerWriter;
	        self::$m_writers['Body'] = self::$m_bodyWriter;
	        self::$m_writers['DateTime'] = self::$m_dateWriter;
	        self::$m_writers['ORBDateTime'] = self::$m_orbdateWriter;
	        self::$m_writers['array'] = self::$m_arrayWriter;
	        self::$m_writers['object'] = self::$m_defaultWriter;
	        self::$m_writers['BodyHolder'] = self::$m_bodyHolderWriter;
	        self::$m_writers['stdClass'] = self::$m_stdClassWriter;
	        self::$m_writers['DataTable'] = self::$m_dataTableAsListWriter;
	        self::$m_writers['ArrayObject'] = self::$m_arrayObjectWriter;
	        self::$m_writers['DOMDocument'] = self::$m_xmlWriter;
	        self::$m_writers['DOMElement'] = self::$m_xmlWriter;
	        self::$m_writers['DOMNode'] = self::$m_xmlWriter;            
	        
	        self::$initialized = true;
    }

    public static function addTypeWriter($mappedType, ITypeWriter $writer)
    {
    	self::init();
        self::$m_writers[$mappedType] = $writer;
    }

    public static function writeObject(&$obj, IProtocolFormatter $formatter)
    {
    	if( !self::$initialized )
    		self::init();

        $writer = self::getWriter($obj);


        if (is_null($writer))
        {
            throw new Exception("Writer is not found.");
        }
		try
        {
			
          if($writer->isReferenceableType())
          {

            $referenceCache = $formatter->getReferenceCache();

            if($referenceCache == null)
            {
              $formatter->resetReferenceCache();

              $referenceCache = $formatter->getReferenceCache();
            }

            if( $referenceCache->hasObject( $obj ) )
            {
                self::$s_referenceWriter->write( $obj, $formatter );

                return;
            }
            else
            {
                $referenceCache->addObject( $obj );
            } 

          }

          $writer->write($obj, $formatter);
        }
        catch (Exception $ex)
        {
             Log::logException(LoggingConstants::EXCEPTION, "Unable to write object", $ex);
        }
    }

    private static function getWriter(&$param)
    {
        if (is_null($param))
        {
            return self::$m_nullWriter;
        }
        else if (is_string($param))
        {
            return self::$m_writers["string"];
        }
        else if (is_float($param) || is_int($param))
        {
            return self::$m_numberWriter;
        }
        else if (is_array($param))
        {
        	if(count($param) == 0)
        		return self::$m_arrayWriter;
        	
            $isDigitsFound = false;
            $isStringsFound = false;

            $keys = array_keys( $param );

            $size = count($keys);
            for( $i = 0; $i < $size; $i++ )
            {
              if(is_string( $keys[ $i ] ))
              {
                $isStringsFound = true;
                break;
              }
             			  
            }

            if($isStringsFound)
                return self::$m_mapWriter;

            return self::$m_arrayWriter;
        }
        else if (is_bool($param))
        {
            return self::$m_booleanWriter;
        }
        else if(is_resource($param))
        {
            return self::$m_resourceWriter;
        }
        else if (is_object($param))
        {
            $type = get_class($param);

            if (isset(self::$m_writers[$type]))
            {
                return self::$m_writers[$type];
            }
            else
            {
                return self::$m_defaultWriter;
            }
        }
        else
        {
            throw new Exception("This functionality is not realized yet.");
        }
    }

    private static function matchInterfaces($interfaces)
    {
        for ($index = 0; $index < count($interfaces); $index ++)
        {
            $writer = self::getWriter($interfaces[$index]);
            if (!is_null($writer))
            {
                return $writer;
            }

            $class = new ReflectionClass($interfaces[$index]);
            $classes = $class->getInterfaces();
            $subInterfaces = array();
            for ($classesIndex = 0; $classesIndex < count($classes); $classesIndex ++)
            {
                $subInterfaces[] = $classes[$classesIndex]->getName();
            }

            $writer = self::matchInterfaces($subInterfaces);
            if (!is_null($writer))
            {
                return $writer;
            }
        }
        return null;
    }

    

    

}

?>
