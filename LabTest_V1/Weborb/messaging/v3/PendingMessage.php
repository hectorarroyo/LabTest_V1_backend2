<?php
class PendingMessage
{
  private /*Hashtable*/ $properties;
  private /*Object*/ $message;
  private /*long*/ $timestamp;

  function __construct(/*Hashtable*/ $properties, /*Object*/ $message )
  {
  	$this->properties = $properties;
    $this->message = $message;
    $this->timestamp = microtime(true);
  }

  public /*long*/function getTimestamp()
  {
    return $this->timestamp;
  }

  public /*Object*/function getMessage()
  {
    return $this->message;
  }
  public /*Hashtable*/function getProperties()
  {
    return $this->properties;
  }
}
?>