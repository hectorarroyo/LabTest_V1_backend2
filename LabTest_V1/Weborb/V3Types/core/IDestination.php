<?php
require_once(WebOrb . "V3Types/core/IServiceHandler.php");
interface IDestination 
{
	/*String*/function getName();
	
    function setName( /*String*/ $name );
    
    /*IServiceHandler*/function getServiceHandler();
    
    function setProperties( /*Hashtable*/ $properties );
    
    /*Hashtable*/function getProperties();
    
    /*String*/function getProperty( /*String*/ $name );
    
    function setServiceHandler( /*IServiceHandler*/ $serviceHandler );
    
    public function setConfigServiceHandler();
    
  	public function messagePublished( /*String*/ $senderId, /*Object*/ $message );
  	
  	public function addMessageEventListener( /*IMessageEventListener*/ $listener );
  	
  	public function removeMessageEventListener( /*IMessageEventListener*/ $listener );  
}
?>
