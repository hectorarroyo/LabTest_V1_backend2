<?php
require_once WebOrb . 'DCA/ReadingIndicator.php';
class ReadingFormatInstance 
{
	private /*int*/ $id;
	private /*String[]*/ $tokens;
	private /*Hashtable<String, ReadingIndicator>*/ $indicators = array();
	
	public function __construct( /*int*/ $id, /*String[]*/ $tokens )
	{
		$this->id = $id;
		$this->tokens = $tokens;
	}
	
	public /*int*/function getId()
	{
		return "_" . $this->id;
	}
	
	public /*String[]*/function getTokens()
	{
		return $this->tokens;
	}
	
	public /*String*/function getRFIString()
	{
		/*String*/ $rfd = "";
		
		for( $i = 0; $i < count($this->tokens); $i++ )
		{
			if( $i != 0 )
				$rfd .= "|";
			
			$rfd .= $this->tokens[ $i ];			
		}
		
		return $rfd;
	}
	
	public /*ArrayList<ReadingIndicator>*/function getIndicators()
	{
		/*ArrayList<ReadingIndicator>*/ $ris = array();
		$ris = $this->indicators;
		
		return $ris;
	}
	
	public /*ReadingIndicator*/function createIndicator( /*String*/ $token, /*String*/ $name, /*String*/ $type )
	{
		/*ReadingIndicator*/ $indicator = null;
		/*int*/ $dimension = count($this->tokens) - 1;
		
		for( $i = 0; $i < count($this->tokens); $i++ )
			if( $this->tokens[ $i ] == $token )
			{
				$dimension = $i;
				
				break;
			}
		
		if( array_key_exists($name, $this->indicators) )
			$indicator = $this->indicators[$name];
		else
		{
			$indicator = new ReadingIndicator( $this, $name, $dimension, $type );
			$this->indicators[$name] = $indicator;
		}
		
		return $indicator;
	}
}
?>