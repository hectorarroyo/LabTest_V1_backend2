<?php
class Channel 
{
	private /*String*/ $id;
	private /*String*/ $endpointUri;
	private /*String*/ $endpointClass = "";
	private /*String*/ $channelClass = "";
	private /*Hashtable*/ $properties = array();
	
	public function __construct( /*String*/ $id, /*String*/ $endpointUri )
	{
		$this->id = $id;
		$this->endpointUri = $endpointUri;
	}
	
	public /*String*/function getId()
	{
		return $this->id;		
	}
	
	public function setId( /*String*/ $id )
	{
		$this->id = $id;		
	}
	
	public /*String*/function getEndpointClass()
	{
		return $this->endpointClass;		
	}
	
	public function setEndpointClass( /*String*/ $endpointClass )
	{
		$this->endpointClass = $endpointClass;		
	}
	
	public /*String*/function getEndpointUri()
	{
		return $this->endpointUri;		
	}
	
	public function setEndpointUri( /*String*/ $endpointUri )
	{
		$this->endpointUri = $endpointUri;		
	}
	
	public /*String*/function getChannelClass()
	{
		return $this->channelClass;		
	}
	
	public function setChannelClass( /*String*/ $channelClass )
	{
		$this->channelClass = $channelClass;		
	}
	
	public /*Hashtable*/function getProperties()
	{
		return $this->properties;		
	}
	
	public function setProperties( /*Hashtable*/ $properties )
	{
		$this->properties = $properties;		
	}
}
?>