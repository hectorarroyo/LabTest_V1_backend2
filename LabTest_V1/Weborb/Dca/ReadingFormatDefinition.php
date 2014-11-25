<?php
require_once WebOrb . 'DCA/ReadingFormatInstance.php';
class ReadingFormatDefinition 
{
	private /*int*/ $id;
	private /*String*/ $type;
	private /*String[]*/ $tokens;
	private /*Hashtable<String, ReadingFormatInstance>*/ $instances = array();
	
	public function __construct( /*int*/ $id, /*String[]*/ $rfdTokens, /*String*/ $type )
	{
		$this->id = $id;
		$this->type = $type;
		$this->tokens = $rfdTokens;
	}
	
	public /*int*/function getId()
	{
		return "_" . $this->id;
	}
	
	public /*String[]*/ function getTokens()
	{
		return $this->tokens;
	}
	
	public /*String*/function getType()
	{
		return $this->type;
	}
	
	public /*ArrayList<ReadingFormatInstance>*/function getInstances()
	{
		/*ArrayList<ReadingFormatInstance>*/ $rfis = array();
		$rfis = $this->instances;
		
		return $rfis;
	}
	
	public /*String*/function getRFDString()
	{
		/*String*/ $rfd = "";
		
		for( /*int*/ $i = 0; $i < count($this->tokens); $i++ )
		{
			if( $i != 0 )
				$rfd .= "|";
			
			$rfd .= $this->tokens[ $i ];			
		}
		
		return $rfd;
	}
	
	public /*ReadingFormatInstance*/function createInstance( /*Hashtable<String, String>*/ $readingDefinition )
	{
		/*ReadingFormatInstance*/ $instance = null;
		/*String[]*/ $rfiTokens = array();
		
		/*String*/ $rfi = "";
		
		for( $i = 0; $i < count($this->tokens); $i++ )
		{
			if( $i != 0 )
				$rfi .= "|";
			
			/*String*/ $rfiToken = $readingDefinition[$this->tokens[ $i ]];
			$rfi .= $rfiToken;
			$rfiTokens[ $i ] = $rfiToken;
		}
		
		if( array_key_exists($rfi, $this->instances) )
			$instance = $this->instances[$rfi];
		else
		{
			$instance = new ReadingFormatInstance( WebORBDataCollectionAgent::getReadingFormatInstanceId(), $rfiTokens );
			$this->instances[$rfi] = $instance;
		}
		return $instance;
	}
}
?>