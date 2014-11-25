<?php
class ReadingIndicator 
{
	private /*ReadingFormatInstance*/ $rfi;
	private /*String*/ $name;
	private /*Object*/ $value;
	private /*int*/ $dimension;
	private /*long*/ $timestamp;
	private /*String*/ $type;
	
	public function __construct( ReadingFormatInstance $rfi, /*String*/ $name, /*int*/ $dimension, /*String*/ $type )
	{
		$this->rfi = $rfi;
		$this->name = $name;
		$this->dimension = $dimension;
		$this->type = $type;
		$this->timestamp = mktime(true);
	}
	
	public /*String*/function getId()
	{
		return $this->rfi->getRFIString() . "|" . $this->name;
	}
	
	public /*String*/function getName()
	{
		return $this->name;
	}
	
	public /*Object*/function getValue()
	{
		return $this->value;
	}
	
	public /*int*/function getDimension()
	{
		return $this->dimension;
	}
	
	public function setTimestamp( /*long*/ $timestamp )
	{
		$this->timestamp = $timestamp;
	}
	
	public /*String*/function getType()
	{
		return $this->type;
	}
	
	public function submitValue( /*Object*/ $value )
	{
		$this->value = $value;
	}
	
	public /*String*/function getReportString()
	{
		return $this->getId() . "|" . $this->timestamp . "|" . $this->value;
	}
}
?>