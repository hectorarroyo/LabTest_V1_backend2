<?php
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NumberReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'BooleanReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'UTFStringReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'AnonymousObjectReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NullReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'PointerReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'BoundPropertyBagReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NotAReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'UndefinedTypeReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'ArrayReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'UndefinedTypeReader.php');//
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'DateReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'LongUTFStringReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'RemoteReferenceReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'XmlDataReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NamedObjectReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3Reader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NullReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'BooleanReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'IntegerReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'NumberReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3StringReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3XmlReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3DateReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3ArrayReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3ObjectReader.php');
	require_once (WebOrb . 'Reader' . DIRECTORY_SEPARATOR . 'V3ByteArrayReader.php');




class MessageDataReader
{
  private static /*ITypeReader[][]*/ $READERS;
  private static /*ITypeReader[]*/ $V1READERS;
  private static /*ITypeReader[]*/ $V3READERS;

  
  	public function __construct()
  	{
		self::initialize();  		
    }
	
    public static function initialize()
    {
    	if (is_null(self::$READERS) || is_null(self::$V1READERS) || is_null(self::$V3READERS))
    	{
	    	self::$V1READERS[ Datatypes::NUMBER_DATATYPE_V1 ] = new NumberReader();
			self::$V1READERS[ Datatypes::BOOLEAN_DATATYPE_V1 ] = new BooleanReader();
			self::$V1READERS[ Datatypes::UTFSTRING_DATATYPE_V1 ] = new UTFStringReader();
			self::$V1READERS[ Datatypes::OBJECT_DATATYPE_V1 ] = new AnonymousObjectReader();
			self::$V1READERS[ Datatypes::NULL_DATATYPE_V1 ] = new NullReader();
			self::$V1READERS[ Datatypes::POINTER_DATATYPE_V1 ] = new PointerReader();
			self::$V1READERS[ Datatypes::OBJECTARRAY_DATATYPE_V1 ] = new BoundPropertyBagReader();
			self::$V1READERS[ Datatypes::ENDOFOBJECT_DATATYPE_V1 ] = new NotAReader();
			self::$V1READERS[ Datatypes::UNKNOWN_DATATYPE_V1 ] = new UndefinedTypeReader();
			self::$V1READERS[ Datatypes::ARRAY_DATATYPE_V1 ] = new ArrayReader();
			self::$V1READERS[ Datatypes::DATE_DATATYPE_V1 ] = new DateReader();
			self::$V1READERS[ Datatypes::LONGUTFSTRING_DATATYPE_V1 ] = new LongUTFStringReader();
			self::$V1READERS[ Datatypes::REMOTEREFERENCE_DATATYPE_V1 ] = new RemoteReferenceReader();
			self::$V1READERS[ Datatypes::RECORDSET_DATATYPE_V1 ] = null;
			self::$V1READERS[ Datatypes::PARSEDXML_DATATYPE_V1 ] = new XmlDataReader();
			self::$V1READERS[ Datatypes::NAMEDOBJECT_DATATYPE_V1 ] = new NamedObjectReader();
			self::$V1READERS[ Datatypes::V3_DATATYPE ] = new V3Reader();
	
			self::$V3READERS[ Datatypes::UNKNOWN_DATATYPE_V3 ] = new UndefinedTypeReader();
			self::$V3READERS[ Datatypes::NULL_DATATYPE_V3 ] = new NullReader();
			self::$V3READERS[ Datatypes::BOOLEAN_DATATYPE_FALSEV3 ] = new BooleanReader( false );
			self::$V3READERS[ Datatypes::BOOLEAN_DATATYPE_TRUEV3 ] = new BooleanReader( true );
			self::$V3READERS[ Datatypes::INTEGER_DATATYPE_V3 ] = new IntegerReader();
			self::$V3READERS[ Datatypes::DOUBLE_DATATYPE_V3 ] = new NumberReader();
			self::$V3READERS[ Datatypes::UTFSTRING_DATATYPE_V3 ] = new V3StringReader();
			self::$V3READERS[ Datatypes::XML_DATATYPE_V3 ] = new V3XmlReader();
			self::$V3READERS[ Datatypes::DATE_DATATYPE_V3 ] = new V3DateReader();
			self::$V3READERS[ Datatypes::ARRAY_DATATYPE_V3 ] = new V3ArrayReader();
			self::$V3READERS[ Datatypes::OBJECT_DATATYPE_V3 ] = new V3ObjectReader();
			self::$V3READERS[ Datatypes::LONGXML_DATATYPE_V3 ] = new V3XmlReader();
			self::$V3READERS[ Datatypes::BYTEARRAY_DATATYPE_V3 ] = new V3ByteArrayReader();
			
		  	self::$READERS[ 0 ] = self::$V1READERS;
		  	self::$READERS[ 1 ] = null;
		  	self::$READERS[ 2 ] = null;
		  	self::$READERS[ 3 ] = self::$V3READERS;
    	}
    }
    
  	public /*Message*/ function readMessage( /*InputStream*/ $stream )
    {
	    /*FlashorbBinaryReader*/$dataStream = new FlashorbBinaryReader( $stream );    
	    /*int*/ $version = $dataStream->readUnsignedShort();
	
	    Log::log( LoggingConstants::DEBUG, "version - " . $version );
	
	    // read headers
	    /*int*/ $totalHeaders = $dataStream->readUnsignedShort();
	    //System.out.println( "totalHeaders - " + totalHeaders );
	    /*Header[]*/ $headers = array();//new Header[ totalHeaders ];
	
	    for( $i = 0; $i < $totalHeaders; $i++ )
	      $headers[ $i ] = $this->readHeader( $dataStream );
	
	    // read body parts
	    /*int*/ $totalBodyParts = $dataStream->readUnsignedShort();
	    
	    Log::log( LoggingConstants::DEBUG, "totalBodies - " . $totalBodyParts );
	
	    /*Body[]*/ $bodies = array();
	
	    for( $i = 0; $i < $totalBodyParts; $i++ )
	      $bodies[ $i ] = $this->readBodyPart( $dataStream );
	      
	   
	    Log::log( LoggingConstants::DEBUG, "returning message" );
	
	    /*Request*/ $request = new Request( $version, $headers, $bodies );
//		var_dump($request);
//    	Log::log(LoggingConstants::MYDEBUG, ob_get_contents());
	    return $request;
    }

  	private /*Header*/function readHeader( FlashorbBinaryReader $stream )
    {	 
    	return new Header( $stream->readUTF(), $stream->readBoolean(), $stream->readInteger( $stream ) /*stream.readInt()*/, $this->readData( $stream ) );
    }

  	private /*Body*/function readBodyPart( FlashorbBinaryReader $stream )
    {	  
    	return new Body( $stream->readUTF(), $stream->readUTF(), $stream->readInteger(), self::readData( $stream ) );
    }
    /*overloading*/
    public static function readData()
    {
    	self::initialize();
    	/*int*/ $numArgs = func_num_args();
    	/*array*/ $args = func_get_args();
    	if ($numArgs == 1 && $args[0] instanceof FlashorbBinaryReader )
    		return self::readData1($args[0]);
    	elseif ($numArgs == 2 && $args[0] instanceof FlashorbBinaryReader && is_int($args[1]))
    		return self::readData3($args[0], $args[1]);
    	elseif ($numArgs == 2 && $args[0] instanceof FlashorbBinaryReader && $args[1] instanceof ParseContext )
    		return self::readData4($args[0], $args[1]);
    	elseif ($numArgs == 3 && $args[0] instanceof FlashorbBinaryReader && $args[1] instanceof ParseContext && is_array($args[2]))
    		return self::readData2($args[0],$args[1],$args[2]);
    	elseif ($numArgs == 3 && is_int($args[0]) && $args[1] instanceof FlashorbBinaryReader && $args[2] instanceof ParseContext)
    		return self::readData5($args[0], $args[1], $args[2]);
    	elseif ($numArgs == 4 && is_int($args[0]) && $args[1] instanceof FlashorbBinaryReader && $args[2] instanceof ParseContext && is_array($args[3]))
    		return self::readData6($args[0], $args[1], $args[2], $args[3]);
    	
    }
    
  
  	public static /*IAdaptingType*/function readData1( FlashorbBinaryReader $reader )
  	{
	  	Log::log( LoggingConstants::DEBUG, "readData( DataInputStream reader )" );	  
      	return self::readData2( $reader, new ParseContext( 0 ), self::$V1READERS );            
	}

	public static /*IAdaptingType*/function readData3( FlashorbBinaryReader $reader, /*int*/$version )
	{
		Log::log( LoggingConstants::DEBUG, "1: The version is " . $version );
		 
		return self::readData2( $reader, new ParseContext( $version ), self::$READERS[ $version ] );
	}

	public static /*IAdaptingType*/ function readData4( FlashorbBinaryReader $reader, ParseContext $parseContext )
	{
		 Log::log( LoggingConstants.DEBUG, "2: The version is " . $parseContext->getVersion() );
		 
		 return self::readData2( $reader, $parseContext, self::$READERS[ $parseContext->getVersion() ] );
	}

	public static /*IAdaptingType*/function readData2( FlashorbBinaryReader $reader, ParseContext $parseContext, /*ITypeReader[]*/ $readers )
	{
		 Log::log( LoggingConstants::DEBUG, "3: The version is " . $parseContext->getVersion() );

		/*int*/ $type = $reader->readByte();
		
		Log::log( LoggingConstants::DEBUG, "The type is " . $type );

		if( $type == 17 )
			return self::$V1READERS[ $type ]->read( $reader, $parseContext );
		else
			return $readers[ $type ]->read( $reader, $parseContext );
	
	}

	public static /*IAdaptingType*/function readData5( /*int*/ $dataType, FlashorbBinaryReader $reader, ParseContext $parseContext )
	{
		 Log::log( LoggingConstants::DEBUG, "4: The version is " . $parseContext->getVersion() );
		 
		return self::readData6( $dataType, $reader, $parseContext, self::$READERS[ $parseContext->getVersion() ] );
	}

	public static /*IAdaptingType*/function readData6( /*int*/ $dataType, FlashorbBinaryReader $reader, ParseContext $parseContext, /*ITypeReader[]*/ $readers )
	{
		 Log.log( ILoggingConstants.DEBUG, "5: The version is " + parseContext.getVersion() );
		 
      return $readers[ $dataType ]->read( $reader, $parseContext );
  }
  }
?>